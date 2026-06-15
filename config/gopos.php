<?php

return [
    'base_url' => rtrim(env('GOPOS_API_URL', 'https://app.gopos.io'), '/'),
    'client_id' => env('GOPOS_CLIENT_ID'),
    'client_secret' => env('GOPOS_CLIENT_SECRET', env('GOPOS_CLIENT_KEY')),
    'organization_id' => env('GOPOS_ORGANIZATION_ID'),
    'timeout' => (int) env('GOPOS_TIMEOUT', 20),
    'token_cache_key' => env('GOPOS_TOKEN_CACHE_KEY', 'gopos_access_token'),
];
