<?php

return [
    // Enable only after mapping the menu and testing acceptance on the real terminal.
    'enabled' => (bool) env('GOORDER_ENABLED', false),
    'menu_reference' => env('GOORDER_MENU_REFERENCE', '108586cd-655d-45fb-b685-3a1834fc6906'),
    'menu_sync_enabled' => (bool) env('GOORDER_MENU_SYNC_ENABLED', true),
    'menu_sync_time' => env('GOORDER_MENU_SYNC_TIME', '03:15'),
    'base_url' => rtrim(env('GOORDER_URL', 'https://umamisushifood.goorder.pl'), '/'),
    'timeout' => 15,
    'poll_seconds' => 10,
    'payment_methods' => [
        'cash' => (int) env('GOORDER_CASH_ID', 2),
        'card' => (int) env('GOORDER_CARD_ID', 3),
    ],
    'payment_references' => [
        'cash' => 'a8312283-3269-4fc5-b680-91743538ba4d',
        'card' => '2cb884ee-5e86-4edb-96a8-42c1857db60e',
    ],
];
