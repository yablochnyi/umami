<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\Delivery\DeliveryQuoteCalculator;
use App\Services\GoPos\GoPosOrderSender;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function show(?string $locale = null)
    {
        $locale = $this->locale($locale);
        app()->setLocale($locale);
        $settings = $this->settings();
        $copy = $this->copy($locale);

        return view('checkout', [
            'locale' => $locale,
            'copy' => $copy,
            'settings' => $settings,
            'localizedUrls' => $this->localizedUrls(),
            'submitUrl' => $locale === 'pl' ? route('checkout.submit') : route('checkout.submit.localized', ['locale' => $locale]),
            'success' => session('checkout_success'),
            'error' => session('checkout_error'),
        ]);
    }

    public function submit(Request $request, GoPosOrderSender $sender, DeliveryQuoteCalculator $deliveryQuote, ?string $locale = null)
    {
        $locale = $this->locale($locale);
        app()->setLocale($locale);

        $copy = $this->copy($locale);
        $settings = $this->settings();

        $data = $request->validate([
            'cart_json' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'wants_invoice' => ['nullable', 'boolean'],
            'nip' => ['nullable', 'required_if:wants_invoice,1', 'string', 'max:32'],
            'delivery_type' => ['required', 'in:pickup,delivery'],
            'fulfillment_type' => ['required', 'in:asap,scheduled'],
            'scheduled_day' => ['nullable', 'required_if:fulfillment_type,scheduled', 'date'],
            'scheduled_time' => ['nullable', 'required_if:fulfillment_type,scheduled', 'date_format:H:i'],
            'payment_type' => ['required', 'in:card,cash'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'required_if:delivery_type,delivery', 'string', Rule::in($this->deliveryCities())],
            'street' => ['nullable', 'required_if:delivery_type,delivery', 'string', 'max:255'],
            'building_number' => ['nullable', 'required_if:delivery_type,delivery', 'string', 'max:50'],
            'apartment_number' => ['nullable', 'string', 'max:50'],
        ]);

        $cart = json_decode($data['cart_json'], true);
        if (! is_array($cart)) {
            return back()->withInput()->with('checkout_error', $copy['invalidCart']);
        }

        $items = $this->resolveCartItems($cart, $locale);
        if ($items->isEmpty()) {
            return back()->withInput()->with('checkout_error', $copy['emptyCart']);
        }

        $subtotal = $items->sum('total');
        if ($data['delivery_type'] === 'delivery' && $settings['minimumDeliveryAmount'] > 0 && $subtotal < $settings['minimumDeliveryAmount']) {
            return back()->withInput()->with('checkout_error', str_replace(':amount', $this->formatPrice($settings['minimumDeliveryAmount'] - $subtotal), $copy['minimumMissing']));
        }

        $scheduledAt = $this->scheduledAt($data, $settings);
        if (($data['fulfillment_type'] ?? null) === 'scheduled') {
            $scheduleError = $this->scheduleValidationMessage($scheduledAt, $data, $settings, $copy);
            if ($scheduleError) {
                return back()->withInput()->with('checkout_error', $scheduleError);
            }
        }

        $quote = $data['delivery_type'] === 'delivery'
            ? $deliveryQuote->quote($settings, $data['city'] ?? null, $data['street'] ?? null, $data['building_number'] ?? null)
            : ['cost' => 0.0, 'distance_km' => null];
        $deliveryCost = $data['delivery_type'] === 'delivery' ? (float) $quote['cost'] : 0.0;
        if ($data['delivery_type'] === 'delivery' && $settings['freeDeliveryFrom'] > 0 && $subtotal >= $settings['freeDeliveryFrom']) {
            $deliveryCost = 0.0;
        }

        $phone = $this->normalizePhone($data['phone']);

        $order = DB::transaction(function () use ($data, $items, $subtotal, $deliveryCost, $settings, $phone, $scheduledAt, $quote): Order {
            $customer = Customer::query()->updateOrCreate(
                ['phone' => $phone],
                [
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'nip' => $data['nip'] ?? null,
                    'city' => $data['city'] ?? null,
                    'street' => $data['street'] ?? null,
                    'building_number' => $data['building_number'] ?? null,
                    'apartment_number' => $data['apartment_number'] ?? null,
                ],
            );

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'number' => 'UMAMI-'.now('Europe/Warsaw')->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                'status' => 'new',
                'delivery_type' => $data['delivery_type'],
                'fulfillment_type' => $data['fulfillment_type'],
                'scheduled_at' => $scheduledAt,
                'payment_type' => $data['payment_type'],
                'wants_invoice' => (bool) ($data['wants_invoice'] ?? false),
                'nip' => $data['nip'] ?? null,
                'city' => $data['city'] ?? null,
                'street' => $data['street'] ?? null,
                'building_number' => $data['building_number'] ?? null,
                'apartment_number' => $data['apartment_number'] ?? null,
                'comment' => $data['comment'] ?? null,
                'subtotal' => $subtotal,
                'delivery_cost' => $deliveryCost,
                'total' => $subtotal + $deliveryCost,
                'free_delivery_from' => $settings['freeDeliveryFrom'],
                'minimum_delivery_amount' => $settings['minimumDeliveryAmount'],
                'gopos_payload' => [
                    'delivery_quote' => $quote,
                ],
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'menu_item_id' => $item['menu_item']->id,
                    'gopos_id' => $item['menu_item']->gopos_id,
                    'name' => $item['name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                    'payload' => [
                        'gopos_tax_id' => $item['menu_item']->gopos_tax_id,
                        'gopos_payload' => $item['menu_item']->gopos_payload,
                    ],
                ]);
            }

            return $order;
        });

        try {
            $sender->send($order);
            $order->refresh();
        } catch (\Throwable $exception) {
            $order->update([
                'status' => 'gopos_error',
                'gopos_error' => $exception->getMessage(),
            ]);

            return redirect($this->checkoutUrl($locale))
                ->withInput()
                ->with('checkout_error', $copy['goposError']);
        }

        return redirect($this->checkoutUrl($locale))
            ->with('checkout_success', str_replace(':number', $this->publicOrderNumber($order), $copy['success']));
    }

    public function streetAutocomplete(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $city = in_array((string) $request->query('city', 'Toruń'), $this->deliveryCities(), true)
            ? (string) $request->query('city', 'Toruń')
            : 'Toruń';
        $normalizedQuery = $this->normalizeStreetSearch($query);
        $local = $city === 'Toruń'
            ? collect($this->torunStreetFallbacks())
                ->filter(fn (string $street): bool => str_contains($this->normalizeStreetSearch($street), $normalizedQuery))
                ->sortBy(function (string $street) use ($normalizedQuery): string {
                    $normalizedStreet = $this->normalizeStreetSearch($street);

                    return (str_starts_with($normalizedStreet, $normalizedQuery) ? '0' : '1').$normalizedStreet;
                })
                ->take(8)
                ->values()
            : collect();

        if ($local->count() >= 5) {
            return response()->json($local);
        }

        $remote = Cache::remember('street-autocomplete:'.$this->normalizeStreetSearch($city).':'.$normalizedQuery, now()->addDay(), function () use ($query, $city): array {
            try {
                $response = Http::timeout(2)
                    ->withHeaders([
                        'User-Agent' => 'UmamiSushiFood/1.0 (https://umamisushifood.pl)',
                    ])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'format' => 'jsonv2',
                        'addressdetails' => 1,
                        'limit' => 10,
                        'countrycodes' => 'pl',
                        'street' => $query,
                        'city' => $city,
                        'country' => 'Polska',
                    ]);

                if (! $response->successful()) {
                    return [];
                }

                return collect($response->json())
                    ->map(fn (array $item): ?string => $item['address']['road'] ?? $item['address']['pedestrian'] ?? $item['address']['footway'] ?? null)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            } catch (\Throwable) {
                return [];
            }
        });

        return response()->json($local->merge($remote)->unique()->take(8)->values());
    }

    public function deliveryQuote(Request $request, DeliveryQuoteCalculator $deliveryQuote)
    {
        $settings = $this->settings();
        $quote = $deliveryQuote->quote(
            $settings,
            (string) $request->query('city', 'Toruń'),
            (string) $request->query('street', ''),
            (string) $request->query('building', ''),
        );

        return response()->json([
            'distance_km' => $quote['distance_km'],
            'cost' => $quote['cost'],
            'formatted_cost' => $this->formatPrice((float) $quote['cost']),
        ]);
    }

    private function resolveCartItems(array $cart, string $locale)
    {
        $ids = collect($cart)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $menuItems = MenuItem::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->whereNotNull('gopos_id')
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function (array $row) use ($menuItems, $locale) {
            $menuItem = $menuItems->get((int) ($row['id'] ?? 0));
            $quantity = max(0, (int) ($row['quantity'] ?? 0));
            if (! $menuItem || $quantity < 1) {
                return null;
            }

            $price = $this->priceAmount($menuItem->price);

            return [
                'menu_item' => $menuItem,
                'name' => $menuItem->getTranslation('name', $locale, false) ?: $menuItem->getTranslation('name', 'pl', false),
                'unit_price' => $price,
                'quantity' => $quantity,
                'total' => $price * $quantity,
            ];
        })->filter()->values();
    }

    private function scheduledAt(array $data, array $settings): ?CarbonImmutable
    {
        if (($data['fulfillment_type'] ?? null) === 'scheduled') {
            return CarbonImmutable::createFromFormat('Y-m-d H:i', $data['scheduled_day'].' '.$data['scheduled_time'], 'Europe/Warsaw');
        }

        return $this->nextAvailableTime($this->fulfillmentOpeningTime($data, $settings), $settings['closingTime']);
    }

    private function scheduleValidationMessage(?CarbonImmutable $scheduledAt, array $data, array $settings, array $copy): ?string
    {
        if (! $scheduledAt || $scheduledAt->lt(CarbonImmutable::now('Europe/Warsaw'))) {
            return $copy['schedulePastTime'];
        }

        $openingTime = $this->fulfillmentOpeningTime($data, $settings);
        $open = CarbonImmutable::createFromFormat('Y-m-d H:i', $scheduledAt->format('Y-m-d').' '.$openingTime, 'Europe/Warsaw');
        $close = CarbonImmutable::createFromFormat('Y-m-d H:i', $scheduledAt->format('Y-m-d').' '.$settings['closingTime'], 'Europe/Warsaw');

        if (! $scheduledAt->betweenIncluded($open, $close)) {
            return strtr($copy['scheduleOutOfHours'], [
                ':open' => $openingTime,
                ':close' => $settings['closingTime'],
            ]);
        }

        return null;
    }

    private function fulfillmentOpeningTime(array $data, array $settings): string
    {
        return ($data['delivery_type'] ?? null) === 'delivery'
            ? $settings['deliveryOpeningTime']
            : $settings['openingTime'];
    }

    private function settings(): array
    {
        $settings = SiteSetting::query()->pluck('value', 'key')->all();

        return [
            'logo' => $this->mediaUrl($settings['logo_image'] ?? 'umami/logo.jpg'),
            'backgroundDesktop' => $this->mediaUrl($settings['background_desktop'] ?? 'umami/tlo3.png'),
            'backgroundMobile' => $this->mediaUrl($settings['background_mobile'] ?? 'umami/tlo4.png'),
            'phone' => $settings['phone'] ?? '+48 513 233 722',
            'phoneHref' => $settings['phone_href'] ?? 'tel:+48513233722',
            'address' => $settings['address'] ?? 'Toruń, ul. Gen. Andersa 72',
            'openingTime' => $this->normalizeTime($settings['opening_time'] ?? '12:00'),
            'deliveryOpeningTime' => $this->normalizeTime($settings['delivery_opening_time'] ?? '13:00'),
            'closingTime' => $this->normalizeTime($settings['closing_time'] ?? '20:30'),
            'deliveryCities' => $this->deliveryCities(),
            'isOrderingOpen' => $this->isOrderingOpen($settings['opening_time'] ?? '12:00', $settings['closing_time'] ?? '20:30'),
            'orderingUnavailableMessage' => $this->orderingUnavailableMessage($settings['opening_time'] ?? '12:00', $settings['closing_time'] ?? '20:30'),
            'deliveryCost' => $this->money($settings['delivery_cost'] ?? '0'),
            'freeDeliveryFrom' => $this->money($settings['free_delivery_from'] ?? '0'),
            'minimumDeliveryAmount' => $this->money($settings['minimum_delivery_amount'] ?? '0'),
            'restaurantLatitude' => $this->money($settings['restaurant_latitude'] ?? '53.0217'),
            'restaurantLongitude' => $this->money($settings['restaurant_longitude'] ?? '18.6676'),
            'deliveryTier1MaxKm' => $this->money($settings['delivery_tier_1_max_km'] ?? '3'),
            'deliveryTier1Cost' => $this->money($settings['delivery_tier_1_cost'] ?? '9.99'),
            'deliveryTier2MaxKm' => $this->money($settings['delivery_tier_2_max_km'] ?? '8'),
            'deliveryTier2Cost' => $this->money($settings['delivery_tier_2_cost'] ?? '14.99'),
            'deliveryTier3Cost' => $this->money($settings['delivery_tier_3_cost'] ?? '24.99'),
            'deliveryTier1ZoneId' => $settings['delivery_tier_1_zone_id'] ?? '2',
            'deliveryTier1ZoneName' => $settings['delivery_tier_1_zone_name'] ?? 'Strefa 1',
            'deliveryTier2ZoneId' => $settings['delivery_tier_2_zone_id'] ?? '3',
            'deliveryTier2ZoneName' => $settings['delivery_tier_2_zone_name'] ?? 'Strefa 2',
            'deliveryTier3ZoneId' => $settings['delivery_tier_3_zone_id'] ?? '4',
            'deliveryTier3ZoneName' => $settings['delivery_tier_3_zone_name'] ?? 'Strefa 3',
            'deliveryTier1Streets' => $settings['delivery_tier_1_streets'] ?? '',
            'deliveryTier2Streets' => $settings['delivery_tier_2_streets'] ?? '',
            'deliveryTier3Streets' => $settings['delivery_tier_3_streets'] ?? '',
        ];
    }

    private function deliveryCities(): array
    {
        return ['Toruń', 'Lubicz Dolny', 'Lubicz Górny', 'Przysiek', 'Kaszczorek'];
    }

    private function publicOrderNumber(Order $order): string
    {
        return (string) ($order->gopos_number ?: $order->gopos_uid ?: $order->gopos_id ?: $order->number);
    }

    private function normalizeStreetSearch(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();
    }

    private function torunStreetFallbacks(): array
    {
        return [
            'Adama Mickiewicza',
            'Akacjowa',
            'Aleja Solidarności',
            'Antczaka',
            'Bażyńskich',
            'Bema',
            'Bolesława Chrobrego',
            'Bolesława Krzywoustego',
            'Broniewskiego',
            'Bulwar Filadelfijski',
            'Bydgoska',
            'Chełmińska',
            'Długa',
            'Dominikańska',
            'Dziewulskiego',
            'Fałata',
            'Forteczna',
            'Gagarina',
            'Gen. Andersa',
            'Grudziądzka',
            'Hallera',
            'Jana Matejki',
            'Jęczmienna',
            'Kaszubska',
            'Katarzyny',
            'Klonowica',
            'Konstytucji 3 Maja',
            'Kościuszki',
            'Kraszewskiego',
            'Krasińskiego',
            'Kręta',
            'Legionów',
            'Lelewela',
            'Lubicka',
            'Łódzka',
            'Małe Garbary',
            'Mickiewicza',
            'Młodzieżowa',
            'Mostowa',
            'Na Skarpie',
            'Olsztyńska',
            'Podgórna',
            'Podmurna',
            'Polna',
            'Poznańska',
            'Prosta',
            'Przedzamcze',
            'Przy Skarpie',
            'Reja',
            'Rydygiera',
            'Rynek Staromiejski',
            'Sienkiewicza',
            'Skłodowskiej-Curie',
            'Słowackiego',
            'Sobieskiego',
            'Szeroka',
            'Szosa Chełmińska',
            'Szosa Lubicka',
            'Świętego Jakuba',
            'Targowa',
            'Traugutta',
            'Trzcinowa',
            'Turystyczna',
            'Uniwersytecka',
            'Wały gen. Sikorskiego',
            'Warszawska',
            'Wielkie Garbary',
            'Włocławska',
            'Wyszyńskiego',
            'Żółkiewskiego',
            'Żwirki i Wigury',
        ];
    }

    private function copy(string $locale): array
    {
        $copy = [
            'pl' => [
                'title' => 'Koszyk i zamówienie',
                'back' => 'Wróć do menu',
                'contact' => 'Kontakt',
                'legalPrivacy' => 'Polityka prywatności',
                'legalCookies' => 'Polityka plików cookie',
                'legalTerms' => 'Regulamin',
                'items' => 'Produkty w koszyku',
                'emptyCart' => 'Koszyk jest pusty.',
                'invalidCart' => 'Nie udało się odczytać koszyka.',
                'details' => 'Dane zamawiającego',
                'name' => 'Imię i nazwisko',
                'email' => 'E-mail',
                'phone' => 'Telefon',
                'invoice' => 'Chcę otrzymać fakturę',
                'nip' => 'NIP',
                'deliveryType' => 'Typ odbioru',
                'pickup' => 'Na wynos',
                'delivery' => 'Dostawa',
                'asap' => 'Jak najszybciej',
                'scheduled' => 'Wybierz dzień i godzinę',
                'day' => 'Dzień',
                'time' => 'Godzina',
                'payment' => 'Płatność',
                'card' => 'Karta',
                'cash' => 'Gotówka',
                'comment' => 'Komentarz',
                'address' => 'Adres dostawy',
                'city' => 'Miasto',
                'street' => 'Ulica',
                'streetPlaceholder' => 'Zacznij wpisywać ulicę w Toruniu',
                'building' => 'Numer domu',
                'apartment' => 'Numer mieszkania',
                'subtotal' => 'Produkty',
                'deliveryCost' => 'Dostawa',
                'total' => 'Razem',
                'freeMissing' => 'Do darmowej dostawy brakuje :amount',
                'freeReady' => 'Dostawa jest darmowa',
                'minimumMissing' => 'Do minimalnej kwoty dostawy brakuje :amount',
                'pickupAvailability' => 'Na wynos dostępne od :open.',
                'deliveryAvailability' => 'Dostawa dostępna w godzinach :open - :close.',
                'scheduleOutOfHours' => 'Wybierz godzinę w godzinach pracy: :open - :close.',
                'schedulePastTime' => 'Nie można wybrać minionej godziny.',
                'submit' => 'Złóż zamówienie',
                'success' => 'Dziękujemy. Zamówienie zostało przyjęte. Numer zamówienia w restauracji: :number.',
                'goposError' => 'Nie udało się przekazać zamówienia do systemu restauracji. Spróbuj ponownie albo zadzwoń do nas.',
            ],
            'uk' => [
                'title' => 'Кошик і замовлення',
                'back' => 'Повернутися до меню',
                'contact' => 'Контакт',
                'legalPrivacy' => 'Політика конфіденційності',
                'legalCookies' => 'Політика cookie',
                'legalTerms' => 'Правила',
                'items' => 'Товари в кошику',
                'emptyCart' => 'Кошик порожній.',
                'invalidCart' => 'Не вдалося прочитати кошик.',
                'details' => 'Дані замовника',
                'name' => "Ім'я та прізвище",
                'email' => 'E-mail',
                'phone' => 'Телефон',
                'invoice' => 'Хочу отримати фактуру',
                'nip' => 'NIP',
                'deliveryType' => 'Тип отримання',
                'pickup' => 'З собою',
                'delivery' => 'Доставка',
                'asap' => 'Якнайшвидше',
                'scheduled' => 'Обрати день і час',
                'day' => 'День',
                'time' => 'Час',
                'payment' => 'Оплата',
                'card' => 'Картка',
                'cash' => 'Готівка',
                'comment' => 'Коментар',
                'address' => 'Адреса доставки',
                'city' => 'Місто',
                'street' => 'Вулиця',
                'streetPlaceholder' => 'Почніть вводити вулицю в Toruń',
                'building' => 'Номер будинку',
                'apartment' => 'Номер квартири',
                'subtotal' => 'Товари',
                'deliveryCost' => 'Доставка',
                'total' => 'Разом',
                'freeMissing' => 'До безкоштовної доставки залишилось :amount',
                'freeReady' => 'Доставка безкоштовна',
                'minimumMissing' => 'До мінімальної суми доставки залишилось :amount',
                'pickupAvailability' => 'Замовлення з собою доступне з :open.',
                'deliveryAvailability' => 'Доставка доступна в години роботи: :open - :close.',
                'scheduleOutOfHours' => 'Оберіть час у межах робочих годин: :open - :close.',
                'schedulePastTime' => 'Не можна вибрати час, який уже минув.',
                'submit' => 'Оформити замовлення',
                'success' => 'Дякуємо. Замовлення прийнято. Номер замовлення в ресторані: :number.',
                'goposError' => 'Не вдалося передати замовлення до системи ресторану. Спробуйте ще раз або зателефонуйте нам.',
            ],
            'en' => [
                'title' => 'Cart and checkout',
                'back' => 'Back to menu',
                'contact' => 'Contact',
                'legalPrivacy' => 'Privacy policy',
                'legalCookies' => 'Cookie policy',
                'legalTerms' => 'Terms',
                'items' => 'Cart items',
                'emptyCart' => 'Your cart is empty.',
                'invalidCart' => 'Could not read the cart.',
                'details' => 'Customer details',
                'name' => 'Full name',
                'email' => 'Email',
                'phone' => 'Phone',
                'invoice' => 'I want an invoice',
                'nip' => 'Tax ID',
                'deliveryType' => 'Delivery type',
                'pickup' => 'Takeaway',
                'delivery' => 'Delivery',
                'asap' => 'As soon as possible',
                'scheduled' => 'Choose day and time',
                'day' => 'Day',
                'time' => 'Time',
                'payment' => 'Payment',
                'card' => 'Card',
                'cash' => 'Cash',
                'comment' => 'Comment',
                'address' => 'Delivery address',
                'city' => 'City',
                'street' => 'Street',
                'streetPlaceholder' => 'Start typing a street in Toruń',
                'building' => 'Building number',
                'apartment' => 'Apartment number',
                'subtotal' => 'Products',
                'deliveryCost' => 'Delivery',
                'total' => 'Total',
                'freeMissing' => ':amount left for free delivery',
                'freeReady' => 'Delivery is free',
                'minimumMissing' => ':amount left for delivery minimum',
                'pickupAvailability' => 'Takeaway is available from :open.',
                'deliveryAvailability' => 'Delivery is available during opening hours: :open - :close.',
                'scheduleOutOfHours' => 'Choose a time within opening hours: :open - :close.',
                'schedulePastTime' => 'You cannot choose a time that has already passed.',
                'submit' => 'Place order',
                'success' => 'Thank you. Your order has been received. Restaurant order number: :number.',
                'goposError' => 'We could not send the order to the restaurant system. Please try again or call us.',
            ],
        ];

        return $copy[$locale] ?? $copy['pl'];
    }

    private function locale(?string $locale): string
    {
        return in_array($locale, ['uk', 'en'], true) ? $locale : 'pl';
    }

    private function localizedUrls(): array
    {
        return [
            'pl' => route('checkout'),
            'uk' => route('checkout.localized', ['locale' => 'uk']),
            'en' => route('checkout.localized', ['locale' => 'en']),
        ];
    }

    private function checkoutUrl(string $locale): string
    {
        return $locale === 'pl' ? route('checkout') : route('checkout.localized', ['locale' => $locale]);
    }

    private function priceAmount(?string $price): float
    {
        return $this->money($price);
    }

    private function money(?string $amount): float
    {
        $normalized = str_replace(',', '.', (string) $amount);

        return max(0, (float) preg_replace('/[^0-9.]/', '', $normalized));
    }

    private function normalizeTime(?string $time): string
    {
        if (preg_match('/^(\d{1,2}):(\d{2})/', (string) $time, $matches)) {
            return sprintf('%02d:%02d', min(23, (int) $matches[1]), min(59, (int) $matches[2]));
        }

        return '12:00';
    }

    private function isOrderingOpen(?string $openingTime, ?string $closingTime): bool
    {
        $openingTime = $this->normalizeTime($openingTime);
        $closingTime = $this->normalizeTime($closingTime);
        $now = CarbonImmutable::now('Europe/Warsaw');
        $open = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$openingTime, 'Europe/Warsaw');
        $close = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$closingTime, 'Europe/Warsaw');

        return $open && $close && $now->betweenIncluded($open, $close);
    }

    private function nextAvailableTime(?string $openingTime, ?string $closingTime): ?CarbonImmutable
    {
        $openingTime = $this->normalizeTime($openingTime);
        $closingTime = $this->normalizeTime($closingTime);
        $now = CarbonImmutable::now('Europe/Warsaw');
        $open = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$openingTime, 'Europe/Warsaw');
        $close = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$closingTime, 'Europe/Warsaw');

        if ($now->lt($open)) {
            return $open;
        }

        if ($now->gt($close)) {
            return $open->addDay();
        }

        return null;
    }

    private function orderingUnavailableMessage(?string $openingTime, ?string $closingTime): string
    {
        $message = trans('site.cart.ordering_unavailable');

        return strtr($message, [
            ':day' => $this->availabilityDayLabel($openingTime, $closingTime),
            ':open' => $this->normalizeTime($openingTime),
            ':close' => $this->normalizeTime($closingTime),
        ]);
    }

    private function availabilityDayLabel(?string $openingTime, ?string $closingTime): string
    {
        $copy = trans('site.cart');
        $closingTime = $this->normalizeTime($closingTime);
        $now = CarbonImmutable::now('Europe/Warsaw');
        $close = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$closingTime, 'Europe/Warsaw');

        return $now->gt($close)
            ? ($copy['day_tomorrow'] ?? 'jutro')
            : ($copy['day_today'] ?? 'dzisiaj');
    }

    private function formatPrice(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, ',', ' '), '0'), ',').' zł';
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        return str_starts_with($digits, '48') ? '+'.$digits : '+48'.$digits;
    }

    private function mediaUrl(?string $path): string
    {
        if (blank($path)) {
            return '';
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')
            ? $path
            : asset('storage/'.$path);
    }

}
