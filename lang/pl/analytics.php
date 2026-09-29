<?php

return [
    'filters' => 'Filtry', 'weekday' => 'Dzień tygodnia',
    'sources' => ['UMAMI_WWW' => 'Strona UMAMI', 'GoPOS' => 'Kasa GoPOS', 'Phone' => 'Telefon', 'Unknown' => 'Nieznany kanał'],
    'title' => 'Analityka sprzedaży', 'order' => 'Zamówienie', 'orders' => 'Zamówienia',
    'date' => 'Data', 'channel' => 'Kanał', 'channels' => 'Sprzedaż według kanałów', 'status' => 'Status',
    'type' => 'Rodzaj zamówienia', 'total' => 'Wartość zamówień', 'paid' => 'Płatności w GoPOS', 'average' => 'Średni rachunek',
    'period' => 'Okres', 'from' => 'Od', 'to' => 'Do', 'payment_status' => 'Status płatności', 'currency' => 'Waluta',
    'amount' => 'Wartość zamówienia', 'min' => 'Minimalna kwota', 'max' => 'Maksymalna kwota',
    'dish' => 'Pozycja menu', 'details' => 'Pozycje zamówienia', 'close' => 'Zamknij', 'export' => 'Eksport CSV',
    'sync' => 'Odśwież dane', 'queued' => 'Aktualizacja w kolejce', 'archive' => 'Historia od',
    'nightly' => 'Aktualizacja nocna', 'updated' => 'Ostatnia pełna synchronizacja',
    'definition' => 'Dane GoPOS według daty utworzenia zamówienia. Domyślnie: zamknięte zamówienia, PLN. Kwoty nie oznaczają zysku ani wypłat platform: nie uwzględniają prowizji i kosztów. Kanały pochodzą z GoPOS; GoOrder może obejmować stronę. Wszystkie wskaźniki uwzględniają filtry i wyszukiwanie. Suma pozycji może różnić się od rachunku przez rabaty i dopłaty.',
    'daily' => 'Dynamika dzienna: wartość i liczba zamówień', 'empty' => 'Brak danych dla wybranych warunków',
    'products' => 'Pozycje z największą wartością sprzedaży', 'quantity' => 'Liczba', 'line_total' => 'Wartość pozycji',
    'weekdays' => 'Zamówienia według dni tygodnia', 'hours' => 'Zamówienia według godzin',
    'statuses' => ['CLOSED' => 'Zamknięte', 'OPENED' => 'Otwarte', 'EXTERNAL' => 'Zewnętrzne', 'VOIDED' => 'Unieważnione', 'REMOVED' => 'Usunięte'],
    'types' => ['DINE_IN' => 'Na miejscu', 'DELIVERY' => 'Dostawa', 'PICK_UP' => 'Na wynos', 'ROOM_SERVICE' => 'Obsługa pokoju'],
    'days' => [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek', 6 => 'Sobota', 7 => 'Niedziela'],
    'sync_states' => ['running' => 'Synchronizacja trwa; dane są niepełne', 'failed' => 'Błąd synchronizacji; część danych nie została odświeżona'],
];
