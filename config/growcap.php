<?php

return [
    'base_url' => env('GROWCAP_API_BASE_URL', env('BACKEND_API_URL', 'https://api.example.com')),
    'token' => env('GROWCAP_API_TOKEN'),
    'timeout' => env('GROWCAP_API_TIMEOUT', 10),
    'endpoints' => [
        'loan' => env('GROWCAP_API_LOAN_ENDPOINT', '/api/prestamos'),
        'loan_plans' => env('GROWCAP_API_LOAN_PLANS_ENDPOINT', '/api/prestamos/planes'),
        'investment' => env('GROWCAP_API_INVESTMENT_ENDPOINT', '/api/inversiones'),
        'investment_plans' => env('GROWCAP_API_INVESTMENT_PLANS_ENDPOINT', '/api/inversiones/planes'),
        'savings' => env('GROWCAP_API_SAVINGS_ENDPOINT', '/api/ahorros'),
        'savings_plans' => env('GROWCAP_API_SAVINGS_PLANS_ENDPOINT', '/api/ahorros/planes'),
        'savings_frequency' => env('GROWCAP_API_SAVINGS_FREQUENCY_ENDPOINT', '/api/ahorros/frecuencia'),
    ],
];
