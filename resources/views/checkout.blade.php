@extends('layouts.site', [
    'metaTitle' => $copy['title'].' | Umami Sushi & Food',
    'robots' => 'noindex, follow',
    'localizedUrls' => $localizedUrls,
    'layoutCss' => '/assets/umami/checkout.css',
    'layoutJs' => '/assets/umami/checkout.js',
    'showCart' => false,
    'bodyData' => [
        'locale' => $locale,
        'background-desktop' => $settings['backgroundDesktop'],
        'background-mobile' => $settings['backgroundMobile'],
        'ordering-open' => $settings['isOrderingOpen'] ? '1' : '0',
        'ordering-unavailable-message' => $settings['orderingUnavailableMessage'],
        'opening-time' => $settings['openingTime'],
        'delivery-opening-time' => $settings['deliveryOpeningTime'],
        'closing-time' => $settings['closingTime'],
        'delivery-cost' => $settings['deliveryCost'],
        'delivery-quote-url' => route('checkout.delivery-quote'),
        'free-delivery-from' => $settings['freeDeliveryFrom'],
        'minimum-delivery-amount' => $settings['minimumDeliveryAmount'],
        'empty-cart' => $copy['emptyCart'],
        'free-missing' => $copy['freeMissing'],
        'free-ready' => $copy['freeReady'],
        'minimum-missing' => $copy['minimumMissing'],
        'pickup-availability' => $copy['pickupAvailability'],
        'delivery-availability' => $copy['deliveryAvailability'],
        'schedule-out-of-hours' => $copy['scheduleOutOfHours'],
        'schedule-past-time' => $copy['schedulePastTime'],
        'clear-cart' => $success ? '1' : '0',
    ],
])

@section('csrf')
@endsection

@section('content')
    <main class="checkout-page">
        @if($success)
            <div class="notice success">{{ $success }}</div>
        @endif
        @if($error)
            <div class="notice error">{{ $error }}</div>
        @endif
        @if($errors->any())
            <div class="notice error">{{ $errors->first() }}</div>
        @endif

        <form class="checkout-layout" method="post" action="{{ $submitUrl }}" id="checkoutForm">
            @csrf
            <input type="hidden" name="cart_json" id="cartJson">

            <section class="checkout-panel">
                <h1>{{ $copy['title'] }}</h1>

                <div class="form-section">
                    <h2>{{ $copy['details'] }}</h2>
                    <label>
                        <span>{{ $copy['name'] }} *</span>
                        <input name="name" value="{{ old('name') }}" autocomplete="name" required>
                    </label>
                    <label>
                        <span>{{ $copy['email'] }} *</span>
                        <input name="email" value="{{ old('email') }}" type="email" autocomplete="email" required>
                    </label>
                    <label>
                        <span>{{ $copy['phone'] }} *</span>
                        <input name="phone" value="{{ old('phone') }}" type="tel" autocomplete="tel" required>
                    </label>
                    <label class="check-row">
                        <input type="checkbox" name="wants_invoice" value="1" id="invoiceToggle" @checked(old('wants_invoice'))>
                        <span>{{ $copy['invoice'] }}</span>
                    </label>
                    <label class="conditional" id="nipField">
                        <span>{{ $copy['nip'] }}</span>
                        <input name="nip" value="{{ old('nip') }}" autocomplete="off">
                    </label>
                </div>

                <div class="form-section">
                    <h2>{{ $copy['deliveryType'] }}</h2>
                    <div class="choice-grid">
                        <label class="choice">
                            <input type="radio" name="delivery_type" value="pickup" @checked(old('delivery_type', 'pickup') === 'pickup')>
                            <span>{{ $copy['pickup'] }}</span>
                        </label>
                        <label class="choice">
                            <input type="radio" name="delivery_type" value="delivery" @checked(old('delivery_type') === 'delivery')>
                            <span>{{ $copy['delivery'] }}</span>
                        </label>
                    </div>

                    <div class="conditional" id="addressFields">
                        <h3>{{ $copy['address'] }}</h3>
                        <label>
                            <span>{{ $copy['city'] }} *</span>
                            <select name="city" id="citySelect" autocomplete="address-level2">
                                @foreach($settings['deliveryCities'] as $city)
                                    <option value="{{ $city }}" @selected(old('city', 'Toruń') === $city)>{{ $city }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="field street-field">
                            <span>{{ $copy['street'] }} *</span>
                            <input name="street" value="{{ old('street') }}" autocomplete="address-line1" placeholder="{{ $copy['streetPlaceholder'] }}" data-street-autocomplete aria-autocomplete="list" aria-controls="streetSuggestions">
                            <div class="street-suggestions" id="streetSuggestions" role="listbox" hidden></div>
                        </div>
                        <div class="two-cols">
                            <label>
                                <span>{{ $copy['building'] }} *</span>
                                <input name="building_number" value="{{ old('building_number') }}" autocomplete="address-line2">
                            </label>
                            <label>
                                <span>{{ $copy['apartment'] }}</span>
                                <input name="apartment_number" value="{{ old('apartment_number') }}">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>{{ $copy['time'] }}</h2>
                    <div class="choice-grid">
                        <label class="choice">
                            <input type="radio" name="fulfillment_type" value="asap" @checked(old('fulfillment_type', 'asap') === 'asap')>
                            <span>{{ $copy['asap'] }}</span>
                        </label>
                        <label class="choice">
                            <input type="radio" name="fulfillment_type" value="scheduled" @checked(old('fulfillment_type') === 'scheduled')>
                            <span>{{ $copy['scheduled'] }}</span>
                        </label>
                    </div>
                    <div class="two-cols conditional" id="scheduleFields">
                        <label>
                            <span>{{ $copy['day'] }}</span>
                            <input type="date" name="scheduled_day" value="{{ old('scheduled_day') }}">
                        </label>
                        <label>
                            <span>{{ $copy['time'] }}</span>
                            <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}">
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h2>{{ $copy['payment'] }}</h2>
                    <div class="choice-grid">
                        <label class="choice">
                            <input type="radio" name="payment_type" value="cash" @checked(old('payment_type', 'cash') === 'cash')>
                            <span>{{ $copy['cash'] }}</span>
                        </label>
                        <label class="choice">
                            <input type="radio" name="payment_type" value="card" @checked(old('payment_type') === 'card')>
                            <span>{{ $copy['card'] }}</span>
                        </label>
                    </div>
                    <label>
                        <span>{{ $copy['comment'] }}</span>
                        <textarea name="comment" rows="4">{{ old('comment') }}</textarea>
                    </label>
                </div>
            </section>

            <aside class="checkout-summary">
                <h2>{{ $copy['items'] }}</h2>
                <div class="summary-items" id="checkoutItems"></div>
                <p class="summary-empty" id="checkoutEmpty" hidden>{{ $copy['emptyCart'] }}</p>
                <p class="summary-hint availability" id="orderingAvailabilityHint" hidden></p>
                <p class="summary-hint" id="freeDeliveryHint" hidden></p>
                <p class="summary-hint error" id="minimumDeliveryHint" hidden></p>
                <dl class="totals">
                    <div>
                        <dt>{{ $copy['subtotal'] }}</dt>
                        <dd id="subtotalValue">0 zł</dd>
                    </div>
                    <div>
                        <dt>{{ $copy['deliveryCost'] }}</dt>
                        <dd id="deliveryValue">0 zł</dd>
                    </div>
                    <div class="grand">
                        <dt>{{ $copy['total'] }}</dt>
                        <dd id="totalValue">0 zł</dd>
                    </div>
                </dl>
                <button class="submit-button" type="submit" id="submitOrder">{{ $copy['submit'] }}</button>
            </aside>
        </form>
    </main>
@endsection
