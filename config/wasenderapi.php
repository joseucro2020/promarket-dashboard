<?php

return [
    'api_key' => env('WASENDERAPI_API_KEY', null),
    'personal_access_token' => env('WASENDERAPI_PERSONAL_ACCESS_TOKEN', null),
    'webhook_secret' => env('WASENDERAPI_WEBHOOK_SECRET', null),
    'base_url' => env('WASENDERAPI_BASE_URL', 'https://www.wasenderapi.com/api'),
];
