<?php

return [
    'base_url' => env('GROWCAP_API_BASE_URL', 'https://api.example.com'),
    'token' => env('GROWCAP_API_TOKEN'),
    'timeout' => env('GROWCAP_API_TIMEOUT', 10),
    'endpoints' => [
        'loan' => env('GROWCAP_API_LOAN_ENDPOINT', '/loans'),
        'investment' => env('GROWCAP_API_INVESTMENT_ENDPOINT', '/api/inversiones'),
        'investment_plans' => env('GROWCAP_API_INVESTMENT_PLANS_ENDPOINT', '/api/inversiones/planes'),
        'savings' => env('GROWCAP_API_SAVINGS_ENDPOINT', '/savings'),
    ],
];
