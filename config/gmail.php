<?php

return [
    'auth_config' => env('GMAIL_AUTH_CONFIG', storage_path('app/gmail/service-account-credentials.json')),

    'impersonated_user' => env('GMAIL_IMPERSONATED_USER'),
];
