<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LegalPageController extends Controller
{
    private const SUPPORTED_LOCALES = ['pl', 'uk', 'en'];

    private const SLUGS = [
        'privacy' => [
            'pl' => 'polityka-prywatnosci',
            'uk' => 'polityka-konfidentsiynosti',
            'en' => 'privacy-policy',
        ],
        'cookies' => [
            'pl' => 'polityka-plikow-cookie',
            'uk' => 'polityka-cookie',
            'en' => 'cookie-policy',
        ],
        'terms' => [
            'pl' => 'regulamin',
            'uk' => 'pravila-korystuvannya',
            'en' => 'terms',
        ],
    ];

    public function __invoke(Request $request)
    {
        $slug = (string) $request->route('slug');
        $locale = $request->route('locale');
        $locale = in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : 'pl';
        $pageKey = $this->pageKeyFromSlug($slug, $locale);

        abort_if($pageKey === null, 404);

        $siteUrl = $this->siteUrl();
        $content = $this->content($locale)[$pageKey];
        $localizedUrls = $this->localizedUrls($siteUrl, $pageKey);

        return view('legal', [
            'locale' => $locale,
            'title' => $content['title'],
            'description' => $content['description'],
            'updatedAt' => $content['updatedAt'],
            'sections' => $content['sections'],
            'localizedUrls' => $localizedUrls,
            'canonicalUrl' => $localizedUrls[$locale],
        ]);
    }

    public static function pageUrls(string $siteUrl): array
    {
        $siteUrl = rtrim($siteUrl, '/');

        return collect(array_keys(self::SLUGS))
            ->mapWithKeys(fn (string $pageKey) => [$pageKey => collect(self::SUPPORTED_LOCALES)
                ->mapWithKeys(fn (string $locale) => [$locale => self::localizedPageUrl($siteUrl, $pageKey, $locale)])
                ->all()])
            ->all();
    }

    private static function localizedPageUrl(string $siteUrl, string $pageKey, string $locale): string
    {
        $prefix = $locale === 'pl' ? '' : '/'.$locale;

        return rtrim($siteUrl, '/').$prefix.'/'.self::SLUGS[$pageKey][$locale];
    }

    private function pageKeyFromSlug(string $slug, string $locale): ?string
    {
        foreach (self::SLUGS as $pageKey => $slugs) {
            if (($slugs[$locale] ?? null) === $slug) {
                return $pageKey;
            }
        }

        return null;
    }

    private function localizedUrls(string $siteUrl, string $pageKey): array
    {
        return collect(self::SUPPORTED_LOCALES)
            ->mapWithKeys(fn (string $locale) => [$locale => self::localizedPageUrl($siteUrl, $pageKey, $locale)])
            ->all();
    }

    private function siteUrl(): string
    {
        return rtrim(
            SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://umamisushifood.pl',
            '/'
        );
    }

    private function content(string $locale): array
    {
        $content = [
            'pl' => [
                'privacy' => [
                    'title' => 'Polityka prywatności',
                    'description' => 'Informacje o zasadach przetwarzania danych osobowych użytkowników strony Umami Sushi & Food Toruń.',
                    'updatedAt' => '3 czerwca 2026',
                    'sections' => [
                        ['heading' => 'Administrator danych', 'body' => ['Administratorem danych związanych z korzystaniem ze strony jest Umami Sushi & Food, ul. Gen. Andersa 72, 87-100 Toruń. W sprawach dotyczących prywatności można kontaktować się telefonicznie: +48 513 233 722.']],
                        ['heading' => 'Zakres danych', 'body' => ['Podczas korzystania ze strony mogą być przetwarzane podstawowe dane techniczne, takie jak adres IP, informacje o urządzeniu, przeglądarce, przybliżonej lokalizacji, data i godzina wizyty oraz odwiedzone podstrony.', 'Jeżeli użytkownik przechodzi do zewnętrznego systemu zamówień online, dalsze przetwarzanie danych odbywa się zgodnie z zasadami tego systemu.']],
                        ['heading' => 'Cele i podstawy przetwarzania', 'body' => ['Dane techniczne są wykorzystywane do zapewnienia bezpieczeństwa, poprawnego działania strony i diagnostyki błędów.', 'Za zgodą użytkownika możemy korzystać z Google Analytics w celu tworzenia statystyk odwiedzin i ulepszania treści strony.']],
                        ['heading' => 'Odbiorcy danych', 'body' => ['Dane mogą być przetwarzane przez dostawców hostingu, narzędzi analitycznych, map, mediów społecznościowych oraz systemu zamówień online, wyłącznie w zakresie potrzebnym do działania strony i usług.']],
                        ['heading' => 'Okres przechowywania', 'body' => ['Dane techniczne i logi serwera są przechowywane przez okres potrzebny do zapewnienia bezpieczeństwa i obsługi strony. Dane analityczne są przechowywane zgodnie z ustawieniami Google Analytics.']],
                        ['heading' => 'Prawa użytkownika', 'body' => ['Użytkownik ma prawo dostępu do danych, sprostowania, usunięcia, ograniczenia przetwarzania, sprzeciwu, przenoszenia danych, wycofania zgody oraz wniesienia skargi do Prezesa Urzędu Ochrony Danych Osobowych.']],
                        ['heading' => 'Dobrowolność danych', 'body' => ['Korzystanie ze strony bez zgody na analitykę jest możliwe. Brak zgody nie wpływa na dostęp do menu, danych kontaktowych ani informacji o restauracji.']],
                    ],
                ],
                'cookies' => [
                    'title' => 'Polityka plików cookie',
                    'description' => 'Informacje o plikach cookie i podobnych technologiach używanych na stronie Umami Sushi & Food Toruń.',
                    'updatedAt' => '3 czerwca 2026',
                    'sections' => [
                        ['heading' => 'Czym są pliki cookie', 'body' => ['Pliki cookie to niewielkie informacje zapisywane w urządzeniu użytkownika przez przeglądarkę. Mogą wspierać działanie strony, zapamiętywać wybory użytkownika lub pomagać w tworzeniu statystyk.']],
                        ['heading' => 'Niezbędne pliki cookie', 'body' => ['Strona może używać plików niezbędnych do prawidłowego działania, bezpieczeństwa, zapamiętania wyboru języka oraz zapamiętania decyzji dotyczącej zgody na cookies. Te pliki nie wymagają zgody użytkownika.']],
                        ['heading' => 'Analityka', 'body' => ['Za zgodą użytkownika strona może uruchomić Google Analytics, aby mierzyć zainteresowanie stroną, menu i podstronami. Analityka nie jest uruchamiana przed wyrażeniem zgody.']],
                        ['heading' => 'Zarządzanie zgodą', 'body' => ['Użytkownik może wybrać opcję „Zgadzam się” albo „Tylko niezbędne” w banerze cookies. Decyzja jest zapisywana w przeglądarce.', 'Zgodę można usunąć poprzez wyczyszczenie danych strony w ustawieniach przeglądarki.']],
                        ['heading' => 'Zewnętrzne serwisy', 'body' => ['Na stronie mogą znajdować się linki do mediów społecznościowych, map i systemu zamówień online. Po przejściu do tych serwisów obowiązują ich własne zasady prywatności i cookies.']],
                    ],
                ],
                'terms' => [
                    'title' => 'Regulamin strony',
                    'description' => 'Warunki korzystania ze strony internetowej Umami Sushi & Food Toruń.',
                    'updatedAt' => '3 czerwca 2026',
                    'sections' => [
                        ['heading' => 'Charakter strony', 'body' => ['Strona internetowa Umami Sushi & Food ma charakter informacyjny. Prezentuje restaurację, menu, dane kontaktowe, galerię oraz odnośnik do zewnętrznego systemu zamówień online.']],
                        ['heading' => 'Menu i ceny', 'body' => ['Dokładamy starań, aby menu, opisy dań i ceny były aktualne. Oferta może ulec zmianie, w szczególności z powodu dostępności składników, sezonowości lub aktualizacji cennika.']],
                        ['heading' => 'Zamówienia online', 'body' => ['Zamówienia online są obsługiwane przez zewnętrzny system wskazany na stronie. Szczegóły zamówienia, płatności, odbioru i ewentualnych reklamacji mogą podlegać zasadom tego systemu oraz ustaleniom z restauracją.']],
                        ['heading' => 'Korzystanie ze strony', 'body' => ['Użytkownik powinien korzystać ze strony zgodnie z prawem, dobrymi obyczajami i bez działań, które mogłyby zakłócić jej funkcjonowanie.']],
                        ['heading' => 'Prawa autorskie', 'body' => ['Materiały zamieszczone na stronie, w tym zdjęcia, teksty, logo i układ graficzny, są chronione prawem. Nie należy ich kopiować ani wykorzystywać bez zgody uprawnionych osób.']],
                        ['heading' => 'Kontakt', 'body' => ['W sprawach dotyczących strony lub oferty restauracji można kontaktować się telefonicznie: +48 513 233 722 albo odwiedzić restaurację przy ul. Gen. Andersa 72 w Toruniu.']],
                    ],
                ],
            ],
            'uk' => [
                'privacy' => [
                    'title' => 'Політика конфіденційності',
                    'description' => 'Інформація про правила обробки персональних даних користувачів сайту Umami Sushi & Food Toruń.',
                    'updatedAt' => '3 червня 2026',
                    'sections' => [
                        ['heading' => 'Адміністратор даних', 'body' => ['Адміністратором даних, пов’язаних із користуванням сайтом, є Umami Sushi & Food, ul. Gen. Andersa 72, 87-100 Toruń. З питань приватності можна звертатися телефоном: +48 513 233 722.']],
                        ['heading' => 'Які дані можуть оброблятися', 'body' => ['Під час користування сайтом можуть оброблятися базові технічні дані: IP-адреса, інформація про пристрій і браузер, приблизна локація, дата й час візиту та переглянуті сторінки.', 'Якщо користувач переходить до зовнішньої системи онлайн-замовлень, подальша обробка даних відбувається відповідно до правил цієї системи.']],
                        ['heading' => 'Мета та підстави обробки', 'body' => ['Технічні дані використовуються для безпеки, коректної роботи сайту та діагностики помилок.', 'За згодою користувача ми можемо використовувати Google Analytics для статистики відвідувань і покращення змісту сайту.']],
                        ['heading' => 'Одержувачі даних', 'body' => ['Дані можуть обробляти постачальники хостингу, аналітичних інструментів, карт, соціальних мереж та системи онлайн-замовлень лише в обсязі, необхідному для роботи сайту й послуг.']],
                        ['heading' => 'Строк зберігання', 'body' => ['Технічні дані та серверні журнали зберігаються протягом часу, необхідного для безпеки й обслуговування сайту. Аналітичні дані зберігаються відповідно до налаштувань Google Analytics.']],
                        ['heading' => 'Права користувача', 'body' => ['Користувач має право на доступ до даних, виправлення, видалення, обмеження обробки, заперечення, перенесення даних, відкликання згоди та подання скарги до польського органу захисту персональних даних.']],
                        ['heading' => 'Добровільність', 'body' => ['Сайтом можна користуватися без згоди на аналітику. Відмова не обмежує доступ до меню, контактів і інформації про ресторан.']],
                    ],
                ],
                'cookies' => [
                    'title' => 'Політика cookie',
                    'description' => 'Інформація про cookie та подібні технології, які використовуються на сайті Umami Sushi & Food Toruń.',
                    'updatedAt' => '3 червня 2026',
                    'sections' => [
                        ['heading' => 'Що таке cookie', 'body' => ['Cookie — це невеликі файли або записи, які браузер зберігає на пристрої користувача. Вони можуть підтримувати роботу сайту, запам’ятовувати вибір користувача або допомагати створювати статистику.']],
                        ['heading' => 'Необхідні cookie', 'body' => ['Сайт може використовувати cookie, необхідні для правильної роботи, безпеки, запам’ятовування мови та рішення щодо згоди на cookies. Такі файли не потребують згоди користувача.']],
                        ['heading' => 'Аналітика', 'body' => ['За згодою користувача сайт може запускати Google Analytics, щоб вимірювати інтерес до сайту, меню та сторінок. Аналітика не запускається до надання згоди.']],
                        ['heading' => 'Керування згодою', 'body' => ['Користувач може обрати “Погоджуюся” або “Лише необхідні” у банері cookies. Рішення зберігається в браузері.', 'Згоду можна скасувати, очистивши дані сайту в налаштуваннях браузера.']],
                        ['heading' => 'Зовнішні сервіси', 'body' => ['На сайті можуть бути посилання на соціальні мережі, карти та систему онлайн-замовлень. Після переходу до цих сервісів діють їхні власні правила приватності й cookies.']],
                    ],
                ],
                'terms' => [
                    'title' => 'Правила користування сайтом',
                    'description' => 'Умови користування сайтом Umami Sushi & Food Toruń.',
                    'updatedAt' => '3 червня 2026',
                    'sections' => [
                        ['heading' => 'Характер сайту', 'body' => ['Сайт Umami Sushi & Food має інформаційний характер. Він представляє ресторан, меню, контактні дані, галерею та посилання на зовнішню систему онлайн-замовлень.']],
                        ['heading' => 'Меню та ціни', 'body' => ['Ми стараємося підтримувати меню, описи страв і ціни актуальними. Пропозиція може змінюватися через доступність інгредієнтів, сезонність або оновлення цін.']],
                        ['heading' => 'Онлайн-замовлення', 'body' => ['Онлайн-замовлення обслуговуються зовнішньою системою, посилання на яку розміщене на сайті. Деталі замовлення, оплати, отримання та можливих рекламацій можуть регулюватися правилами цієї системи й домовленостями з рестораном.']],
                        ['heading' => 'Користування сайтом', 'body' => ['Користувач повинен користуватися сайтом відповідно до закону, добрих звичаїв і без дій, які можуть порушити його роботу.']],
                        ['heading' => 'Авторські права', 'body' => ['Матеріали на сайті, зокрема фото, тексти, логотип і графічне оформлення, охороняються законом. Їх не слід копіювати або використовувати без згоди уповноважених осіб.']],
                        ['heading' => 'Контакт', 'body' => ['З питань сайту або пропозиції ресторану можна звертатися телефоном: +48 513 233 722 або відвідати ресторан за адресою ul. Gen. Andersa 72 у Торуні.']],
                    ],
                ],
            ],
            'en' => [
                'privacy' => [
                    'title' => 'Privacy policy',
                    'description' => 'Information about the processing of personal data of users of the Umami Sushi & Food Toruń website.',
                    'updatedAt' => '3 June 2026',
                    'sections' => [
                        ['heading' => 'Data controller', 'body' => ['The controller of data related to the use of this website is Umami Sushi & Food, ul. Gen. Andersa 72, 87-100 Toruń. For privacy matters, you can contact us by phone: +48 513 233 722.']],
                        ['heading' => 'Data scope', 'body' => ['When using the website, basic technical data may be processed, such as IP address, device and browser information, approximate location, date and time of visit, and visited pages.', 'If a user proceeds to an external online ordering system, further data processing is governed by the rules of that system.']],
                        ['heading' => 'Purposes and legal basis', 'body' => ['Technical data is used to ensure security, proper website operation and error diagnostics.', 'With the user’s consent, we may use Google Analytics to create visit statistics and improve website content.']],
                        ['heading' => 'Data recipients', 'body' => ['Data may be processed by providers of hosting, analytics tools, maps, social media and the online ordering system, only to the extent necessary for the website and services to operate.']],
                        ['heading' => 'Retention period', 'body' => ['Technical data and server logs are stored for the period necessary to ensure security and website maintenance. Analytics data is stored according to Google Analytics settings.']],
                        ['heading' => 'User rights', 'body' => ['The user has the right to access, rectify, erase, restrict processing, object, transfer data, withdraw consent and lodge a complaint with the Polish data protection authority.']],
                        ['heading' => 'Voluntary use', 'body' => ['The website can be used without consent to analytics. Refusing consent does not affect access to the menu, contact details or restaurant information.']],
                    ],
                ],
                'cookies' => [
                    'title' => 'Cookie policy',
                    'description' => 'Information about cookies and similar technologies used on the Umami Sushi & Food Toruń website.',
                    'updatedAt' => '3 June 2026',
                    'sections' => [
                        ['heading' => 'What cookies are', 'body' => ['Cookies are small pieces of information stored on the user’s device by the browser. They can support website operation, remember user choices or help create statistics.']],
                        ['heading' => 'Essential cookies', 'body' => ['The website may use cookies necessary for proper operation, security, remembering language choice and remembering the cookie consent decision. These cookies do not require user consent.']],
                        ['heading' => 'Analytics', 'body' => ['With the user’s consent, the website may run Google Analytics to measure interest in the website, menu and pages. Analytics is not launched before consent is given.']],
                        ['heading' => 'Managing consent', 'body' => ['The user can choose “I agree” or “Essential only” in the cookie banner. The decision is stored in the browser.', 'Consent can be removed by clearing the website data in browser settings.']],
                        ['heading' => 'External services', 'body' => ['The website may contain links to social media, maps and the online ordering system. After visiting those services, their own privacy and cookie rules apply.']],
                    ],
                ],
                'terms' => [
                    'title' => 'Terms of website use',
                    'description' => 'Terms of use for the Umami Sushi & Food Toruń website.',
                    'updatedAt' => '3 June 2026',
                    'sections' => [
                        ['heading' => 'Website character', 'body' => ['The Umami Sushi & Food website is informational. It presents the restaurant, menu, contact details, gallery and a link to an external online ordering system.']],
                        ['heading' => 'Menu and prices', 'body' => ['We make efforts to keep the menu, dish descriptions and prices up to date. The offer may change, especially due to ingredient availability, seasonality or price updates.']],
                        ['heading' => 'Online orders', 'body' => ['Online orders are handled by the external system linked on the website. Order details, payment, pickup and possible complaints may be governed by the rules of that system and arrangements with the restaurant.']],
                        ['heading' => 'Using the website', 'body' => ['The user should use the website in accordance with the law, good practices and without actions that could disrupt its operation.']],
                        ['heading' => 'Copyright', 'body' => ['Materials on the website, including photos, texts, logo and graphic layout, are protected by law. They should not be copied or used without consent of the authorized persons.']],
                        ['heading' => 'Contact', 'body' => ['For matters related to the website or restaurant offer, please call +48 513 233 722 or visit the restaurant at ul. Gen. Andersa 72 in Toruń.']],
                    ],
                ],
            ],
        ];

        return $content[$locale] ?? $content['pl'];
    }
}
