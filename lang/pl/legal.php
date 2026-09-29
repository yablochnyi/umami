<?php

return [
    'updated_label' => 'Ostatnia aktualizacja:',
    'updated' => '29 września 2026',
    'contents' => 'Spis treści',
    'related' => 'Powiązane dokumenty',
    'cookie_settings' => 'Ustawienia cookies',
    'map_load' => 'Pokaż mapę Google',
    'map_notice' => 'Po włączeniu mapy Google otrzyma Twój adres IP i dane przeglądarki. Mapa może korzystać z cookies zgodnie z polityką Google.',
    'privacy' => [
        'title' => 'Polityka prywatności',
        'description' => 'Jak korzystamy z danych podczas przeglądania menu, składania zamówień i kontaktu z Umami Sushi & Food w Toruniu.',
        'sections' => [
            ['heading' => '1. Kontakt w sprawach danych', 'body' => [
                'Administratorem danych są wspólniczki prowadzące działalność jako DARIA JANZ, MARYNA ZASLAVSKA SPÓŁKA CYWILNA, NIP 9562405793, adres: ul. gen. Karola Kniaziewicza 52A/3, 87-100 Toruń, Polska. Restauracja Umami Sushi & Food znajduje się przy ul. Gen. Andersa 72, 87-100 Toruń.',
                'W sprawach danych i zamówień skontaktuj się z restauracją: +48 513 233 722 lub korespondencyjnie pod adresem firmy wskazanym powyżej.',
            ]],
            ['heading' => '2. Jakie dane przetwarzamy', 'body' => [
                'Przy zamówieniu zapisujemy imię i nazwisko, telefon, e-mail, zamówione pozycje, ilości, ceny, sposób płatności i odbioru, wybrany termin oraz status zamówienia. Przy dostawie potrzebujemy adresu; przy fakturze także NIP. Zapisujemy również uwagi podane w formularzu. Nie wpisuj do nich zbędnych danych wrażliwych.',
                'Serwer może rejestrować adres IP, czas żądania, odwiedzaną stronę i informacje o przeglądarce potrzebne do diagnostyki. Formularz nie zbiera numeru karty ani kodu CVV. Statystyki sprzedaży obejmują źródło, wartości i pozycje zamówień; osobna baza raportowa nie kopiuje danych kontaktowych klientów.',
            ]],
            ['heading' => '3. Cele i podstawy prawne', 'body' => [
                'Obsługa zamówienia, dostawa i kontakt w jego sprawie służą wykonaniu umowy lub działaniom przed jej zawarciem (art. 6 ust. 1 lit. b RODO). Dokumentacja podatkowa i rozliczeniowa wynika z obowiązków prawnych (lit. c). Bezpieczeństwo, obsługa roszczeń i wewnętrzna analiza sprzedaży służą uzasadnionym interesom administratora (lit. f).',
                'Opcjonalne statystyki odwiedzin Google Analytics uruchamiamy wyłącznie po zgodzie (lit. a). Zgoda na analitykę nie jest warunkiem zakupu. Nie podejmujemy wobec klientów wyłącznie zautomatyzowanych decyzji wywołujących skutki prawne.',
            ]],
            ['heading' => '4. Dostawcy usług i przekazywanie danych', 'body' => [
                'Dostęp w niezbędnym zakresie mogą mieć upoważnieni pracownicy, dostawcy hostingu i obsługi technicznej, system GoPOS oraz GoOrder, gdy obsługuje dane zamówienie, dostawcy realizujący dostawę i obsługa księgowa. Dane mogą otrzymać również uprawnione organy.',
                'Przy wycenie dostawy miejscowość, ulica i numer budynku mogą zostać przesłane do Nominatim (OpenStreetMap) w celu ustalenia współrzędnych. W tym zapytaniu nie przesyłamy nazwiska, e-maila, telefonu ani numeru mieszkania; wynik jest buforowany przez miesiąc.',
                'Google otrzymuje dane techniczne po zgodzie na analitykę lub po samodzielnym włączeniu mapy. Usługi Google mogą wiązać się z przetwarzaniem poza EOG. Informacje o lokalizacjach i zabezpieczeniach transferu znajdziesz w polityce Google: https://policies.google.com/privacy. Przejście do Wolt, Pyszne lub mediów społecznościowych podlega również zasadom wybranego serwisu.',
            ]],
            ['heading' => '5. Jak długo przechowujemy dane', 'body' => [
                'Okres przechowywania zależy od celu: realizacji zamówienia, rozpatrzenia reklamacji, obowiązkowych terminów przechowywania dokumentów księgowych i przedawnienia roszczeń. Nie wszystkie dane wymagają tak samo długiego przechowywania. Zakończenie zamówienia nie usuwa automatycznie jego historii.',
                'Logi techniczne zależą od konfiguracji hostingu, a czas przechowywania zdarzeń analitycznych od ustawień usługi Google Analytics. Informację o okresie właściwym dla Twoich danych uzyskasz w restauracji. Zasady pamięci przeglądarki opisuje polityka cookies.',
            ]],
            ['heading' => '6. Twoje prawa i dobrowolność', 'body' => [
                'Na zasadach RODO możesz żądać dostępu, kopii, sprostowania, usunięcia lub ograniczenia przetwarzania danych, a w odpowiednich przypadkach przeniesienia danych i wnieść sprzeciw wobec przetwarzania opartego na uzasadnionym interesie. Usunięcie może być ograniczone obowiązkami prawnymi. Możemy poprosić o informacje niezbędne do potwierdzenia tożsamości.',
                'Zgodę na analitykę wycofasz w „Ustawieniach cookies” w stopce. Nie wpływa to na zgodność z prawem wcześniejszego przetwarzania. Masz prawo skargi do Prezesa UODO: https://uodo.gov.pl. Dane zamówienia podajesz dobrowolnie, ale bez wymaganych danych nie możemy go obsłużyć. Menu można przeglądać bez zamawiania i bez zgody na analitykę.',
            ]],
        ],
    ],
    'cookies' => [
        'title' => 'Polityka plików cookie',
        'description' => 'Co zapisujemy w przeglądarce, do czego służą te dane i jak zmienić swoją decyzję.',
        'sections' => [
            ['heading' => '1. Cookies i pamięć przeglądarki', 'body' => [
                'Cookies to niewielkie dane zapisywane przez stronę w przeglądarce. Korzystamy też z localStorage, które pozostaje po zamknięciu przeglądarki, oraz sessionStorage, działającego w ramach karty. Część zapisów jest potrzebna do zamówienia, a analityka jest opcjonalna.',
            ]],
            ['heading' => '2. Niezbędne dane', 'body' => [
                'Cookie sesji aplikacji i XSRF-TOKEN wspierają sesję, formularze i ochronę przed fałszywymi żądaniami. Czas ważności sesji wynika z konfiguracji serwera i może być odnawiany podczas korzystania ze strony.',
                'umami_cart przechowuje koszyk w localStorage. umami_cookie_consent zapamiętuje wybór cookies w localStorage do zmiany lub usunięcia danych strony oraz w zapasowym cookie do 12 miesięcy. Nie służą reklamie.',
                'umami_last_order_status przechowuje odnośnik i stan ostatniego zamówienia; mechanizm powrotu sprawdza ważność przez 7 dni od ostatniej zapisanej aktualizacji lub przewidywanego przygotowania, zależnie od tego, co nastąpi później. Techniczne znaczniki potwierdzenia zamówienia zapobiegają ponownemu opróżnieniu koszyka. Zapisy localStorage pozostają do usunięcia lub zastąpienia; nie udostępniaj linku do swojego zamówienia.',
            ]],
            ['heading' => '3. Opcjonalna analityka Google', 'body' => [
                'Jeśli analityka jest skonfigurowana, dopiero po kliknięciu „Zgadzam się” pobieramy skrypt Google Analytics. Może on przetwarzać identyfikatory przeglądarki, odwiedzane strony, zdarzenia i dane urządzenia. Cookies _ga i _ga_* zwykle mają okres do 2 lat, z możliwością odnowienia zgodnie z ustawieniami Google. Znacznik umami_purchase_tracked_* w sessionStorage zapobiega ponownemu zliczeniu zakupu w tej samej karcie.',
                'Zgoda dotyczy statystyki odwiedzin, nie personalizacji reklam. Nie uruchamiamy Google Analytics na prywatnej stronie śledzenia zamówienia.',
            ]],
            ['heading' => '4. Zmiana i wycofanie zgody', 'body' => [
                '„Tylko niezbędne” odmawia analityki. Obie opcje pozostawiają dostęp do menu i zamówień. W dowolnym momencie otwórz „Ustawienia cookies” w stopce, a następnie wybierz „Tylko niezbędne”, aby wycofać zgodę.',
                'Przy wycofaniu wyłączamy dalsze zbieranie i usuwamy dostępne dla tej strony cookies _ga. Zmiana może odświeżyć stronę, dlatego najpierw dokończ edycję formularza. Nie usuwa to danych przesłanych wcześniej do Google. Pełną pamięć strony możesz usunąć w przeglądarce; usuwa to także koszyk i zapamiętany odnośnik do zamówienia. Wybór obowiązuje w danej przeglądarce.',
            ]],
            ['heading' => '5. Mapy i zewnętrzne strony', 'body' => [
                'Mapa Google nie ładuje się automatycznie. Włączasz ją osobnym przyciskiem przy mapie, po zapoznaniu się z informacją o przesłaniu danych do Google. Wycofanie zgody na analitykę nie usuwa wcześniejszych danych mapy; odświeżenie strony ponownie blokuje mapę.',
                'Linki do innych stron otwierają usługi z własnymi zasadami cookies. Szczegóły przetwarzania danych i kontakt do restauracji podajemy w polityce prywatności.',
            ]],
        ],
    ],
    'terms' => [
        'title' => 'Regulamin strony i zamówień',
        'description' => 'Zasady korzystania z menu, składania zamówień, odbioru, dostawy oraz zgłaszania problemów.',
        'sections' => [
            ['heading' => '1. Restauracja i kontakt', 'body' => [
                'Sprzedawcą są wspólniczki prowadzące działalność jako DARIA JANZ, MARYNA ZASLAVSKA SPÓŁKA CYWILNA, NIP 9562405793, adres: ul. gen. Karola Kniaziewicza 52A/3, 87-100 Toruń, Polska.',
                'Strona umamisushifood.pl prezentuje ofertę i umożliwia składanie zamówień do restauracji Umami Sushi & Food, ul. Gen. Andersa 72, 87-100 Toruń. W sprawach zamówień, zmian, reklamacji i działania strony zadzwoń pod +48 513 233 722 lub napisz pod adres firmy. Lokal i adres firmy to różne adresy; odbiór zamówień odbywa się przy ul. Gen. Andersa 72.',
            ]],
            ['heading' => '2. Menu, ceny i alergeny', 'body' => [
                'Ceny są podane w złotych polskich (PLN) z uwzględnieniem podatków. Koszt dostawy i łączna kwota są przedstawiane przed wysłaniem zamówienia. Dostępność dań, lunchów i wariantów może zależeć od dnia, godziny i zapasów. Wiążące są warunki potwierdzonego zamówienia, nie późniejsze zmiany cennika.',
                'Zdjęcia mają charakter prezentacyjny. Informacje o składnikach i alergenach sprawdź w opisie dania, a w razie wątpliwości skontaktuj się z personelem przed zamówieniem. Nie zakładaj, że brak alergenu w skróconym opisie oznacza jego nieobecność lub brak kontaktu krzyżowego.',
            ]],
            ['heading' => '3. Składanie i potwierdzanie zamówień', 'body' => [
                'Wybierz dania i ilości, sprawdź koszyk, podaj dane kontaktowe oraz wybierz odbiór lub dostawę, termin i sposób płatności. Popraw ewentualne błędy przed użyciem przycisku zamówienia. Wysłanie zamówienia z obowiązkiem zapłaty jest zgłoszeniem chęci zakupu na przedstawionych warunkach.',
                'Komunikat o technicznym wysłaniu lub oczekiwaniu nie oznacza jeszcze akceptacji przez personel. O przyjęciu i terminie informuje status zamówienia lub restauracja. Przy braku potwierdzenia skontaktuj się telefonicznie, zanim złożysz zamówienie ponownie. Zmiana ceny lub pozycji wymaga Twojej zgody.',
            ]],
            ['heading' => '4. Płatność, odbiór i dostawa', 'body' => [
                'Dostępne sposoby płatności są widoczne w formularzu; wybór karty nie oznacza obciążenia karty przez ten formularz. Nie podawaj danych karty w uwagach. NIP do faktury podaj podczas składania zamówienia.',
                'Dostawa jest dostępna w obsługiwanych strefach i godzinach, z minimalną wartością oraz opłatą wskazaną przed wysłaniem. Odbiór osobisty odbywa się w restauracji. Podaj prawidłowy adres i telefon, pod którym można uzgodnić odbiór.',
                'Termin przygotowania i licznik są informacją o przewidywanym czasie, nie potwierdzeniem fizycznego doręczenia. W razie opóźnienia lub problemów skontaktuj się z restauracją. Godziny podajemy według czasu w Polsce.',
            ]],
            ['heading' => '5. Zmiany, anulowanie i odstąpienie', 'body' => [
                'Prośbę o zmianę lub anulowanie zgłoś jak najszybciej telefonicznie. Możliwość jej uwzględnienia zależy od etapu realizacji i ustaleń z restauracją.',
                'Ustawowe 14-dniowe odstąpienie bez przyczyny nie przysługuje w odniesieniu do towarów szybko psujących się lub o krótkim terminie przydatności, w tym świeżo przygotowanych posiłków (art. 38 ust. 1 pkt 4 ustawy o prawach konsumenta). Nie wyłącza to prawa do reklamacji ani innych ustawowych uprawnień konsumenta.',
            ]],
            ['heading' => '6. Reklamacje', 'body' => [
                'Jeżeli zamówienie jest niekompletne, niezgodne z ustaleniami lub budzi zastrzeżenia jakościowe, skontaktuj się z restauracją. Podaj numer lub datę zamówienia, opis problemu, oczekiwane rozwiązanie i dane do odpowiedzi. Zdjęcie może pomóc, ale nie jest bezwzględnym warunkiem reklamacji.',
                'Możesz zgłosić problem telefonicznie lub pisemnie pod adresem restauracji. Do reklamacji konsumenta stosuje się ustawowe zasady i terminy, w tym co do zasady 14 dni na odpowiedź. Zachowujesz prawo skorzystania z pomocy rzecznika konsumentów i pozasądowych sposobów rozwiązywania sporów.',
            ]],
            ['heading' => '7. Korzystanie ze strony i wersje dokumentu', 'body' => [
                'Do zamawiania potrzebne są połączenie z internetem, aktualna przeglądarka, JavaScript i możliwość korzystania z niezbędnej pamięci strony. Nie przesyłaj treści bezprawnych i nie zakłócaj działania serwisu. Treści i zdjęcia podlegają ochronie prawnej, z zachowaniem dozwolonego użytku.',
                'Zamówienia składane bezpośrednio na zewnętrznych platformach podlegają również warunkom przedstawionym w tych platformach. Polityka prywatności i cookies opisują przetwarzanie danych. Dokument jest dostępny po polsku, ukraińsku i angielsku. Aktualizacje nie zmieniają warunków wcześniej potwierdzonych zamówień ani obowiązkowych praw konsumenta.',
            ]],
        ],
    ],
];
