<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gmail Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | This application supports two authentication modes for the Gmail API:
    |
    | 1. 'oauth' (Development)
    |    - Used with personal Gmail accounts via OAuth 2.0.
    |    - Requires a Web Application OAuth 2.0 Client ID from Google Cloud Console.
    |    - Run `php artisan gmail:authorize` after configuring to obtain a refresh token.
    |    - The refresh token is stored in the gmail_tokens database table.
    |
    | 2. 'service_account' (Production)
    |    - Used with Google Workspace accounts via domain-wide delegation.
    |    - Requires a service account JSON key with Gmail API scopes enabled.
    |    - The impersonated user must be a real mailbox in your Workspace domain.
    |    - Set GMAIL_AUTH_CONFIG to the storage path of the JSON key.
    |    - Set GMAIL_IMPERSONATED_USER to the Workspace user email to impersonate.
    |
    | To switch modes, update GMAIL_AUTH_MODE in your .env file.
    |
    */

    'auth_mode' => env('GMAIL_AUTH_MODE', 'oauth'),

    /*
    |--------------------------------------------------------------------------
    | OAuth 2.0 Credentials (Development Mode)
    |--------------------------------------------------------------------------
    |
    | These values come from a Desktop OAuth 2.0 Client ID created in
    | the Google Cloud Console under APIs & Services → Credentials.
    |
    | Run `php artisan gmail:authorize` after configuring to obtain
    | a refresh token. The token is stored in the gmail_tokens table.
    |
    */

    'client_id' => env('GMAIL_CLIENT_ID'),

    'client_secret' => env('GMAIL_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials (Production Mode)
    |--------------------------------------------------------------------------
    |
    | These values are used when auth_mode is 'service_account'.
    |
    | Steps to set up:
    | 1. Create a service account in Google Cloud Console (IAM & Admin)
    | 2. Enable domain-wide delegation for the service account
    | 3. In Google Workspace Admin Console, grant the service account access
    |    to the Gmail API scopes (https://www.googleapis.com/auth/gmail.readonly,
    |    https://www.googleapis.com/auth/gmail.modify)
    | 4. Download the service account JSON key and place it in storage/app/gmail/
    | 5. Set GMAIL_IMPERSONATED_USER to a real mailbox in your domain
    |
    */

    'auth_config' => env('GMAIL_AUTH_CONFIG', storage_path('app/gmail/service-account-credentials.json')),

    'impersonated_user' => env('GMAIL_IMPERSONATED_USER'),
];
