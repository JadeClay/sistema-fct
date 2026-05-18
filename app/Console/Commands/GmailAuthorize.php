<?php

namespace App\Console\Commands;

use App\Models\GmailToken;
use Google\Client;
use Google\Service\Gmail;
use Illuminate\Console\Command;

class GmailAuthorize extends Command
{
    protected $signature = 'gmail:authorize';

    protected $description = 'Authorize Gmail API access via OAuth 2.0 and store the refresh token';

    public function handle(): int
    {
        $existingToken = GmailToken::first()?->refresh_token;

        if ($existingToken) {
            $this->warn('A refresh token already exists. Re-authorizing will replace it.');
            if (! $this->confirm('Do you want to continue?')) {
                return self::SUCCESS;
            }
        }

        $client = new Client;
        $client->setClientId(config('gmail.client_id'));
        $client->setClientSecret(config('gmail.client_secret'));
        $client->setRedirectUri(config('gmail.redirect_uri'));
        $client->addScope(Gmail::GMAIL_READONLY);
        $client->addScope(Gmail::GMAIL_MODIFY);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();

        $this->info('Open the following URL in your browser to authorize Gmail access:');
        $this->line('');
        $this->line($authUrl);
        $this->line('');
        $this->info('After granting access, Google will display an authorization code.');
        $this->info('Copy that code and paste it below.');

        $authCode = $this->ask('Authorization code');

        if (! $authCode) {
            $this->error('No authorization code provided.');

            return self::FAILURE;
        }

        $token = $client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($token['error'])) {
            $this->error('Failed to exchange authorization code: '.($token['error_description'] ?? $token['error']));

            return self::FAILURE;
        }

        $refreshToken = $token['refresh_token'] ?? '';

        if (! $refreshToken) {
            $this->error('No refresh token received. Ensure "offline" access type is set in Google Cloud Console.');

            return self::FAILURE;
        }

        GmailToken::first()->update(['refresh_token' => $refreshToken]);

        $this->info('✅ Gmail authorized successfully! You can now run: php artisan fetch:emails');

        return self::SUCCESS;
    }
}
