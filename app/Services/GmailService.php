<?php

namespace App\Services;

use App\Models\GmailToken;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Collection;

/**
 * Service for interacting with the Gmail API.
 *
 * Supports two authentication modes:
 *
 * 1. OAuth 2.0 (development)
 *    - Used with personal Gmail accounts.
 *    - Configure GMAIL_CLIENT_ID and GMAIL_CLIENT_SECRET.
 *    - Run `php artisan gmail:authorize` once to obtain a refresh token.
 *
 * 2. Service Account (production)
 *    - Used with Google Workspace accounts via domain-wide delegation.
 *    - Configure GMAIL_AUTH_CONFIG and GMAIL_IMPERSONATED_USER.
 *    - No manual authorization needed.
 *
 * The active mode is determined by the GMAIL_AUTH_MODE env variable
 * in config/gmail.php.
 */
class GmailService
{
    private Client $client;

    private Gmail $gmail;

    public function __construct()
    {
        $this->client = new Client;

        if (config('gmail.auth_mode') === 'oauth') {
            $this->client->setClientId(config('gmail.client_id'));
            $this->client->setClientSecret(config('gmail.client_secret'));
            $this->client->setRedirectUri(config('gmail.redirect_uri'));

            $token = GmailToken::first();

            if (! $token?->refresh_token) {
                throw new \RuntimeException(
                    'No Gmail refresh token found. Run `php artisan gmail:authorize` first.'
                );
            }

            $this->client->fetchAccessTokenWithRefreshToken($token->refresh_token);
        } else {
            $this->client->setAuthConfig(config('gmail.auth_config'));
            $this->client->setSubject(config('gmail.impersonated_user'));
        }

        $this->client->addScope(Gmail::GMAIL_READONLY);
        $this->client->addScope(Gmail::GMAIL_MODIFY);

        $this->gmail = new Gmail($this->client);
    }

    public function getUnreadMessages(): Collection
    {
        $response = $this->gmail->users_messages->listUsersMessages('me', [
            'q' => 'is:unread',
        ]);

        return collect($response->getMessages() ?? []);
    }

    /** @return array{subject: string, sender: string, body: string} */
    public function getMessageDetail(string $messageId): array
    {
        $message = $this->gmail->users_messages->get('me', $messageId, [
            'format' => 'full',
        ]);

        return [
            'subject' => $this->getHeader($message, 'Subject'),
            'sender' => $this->getHeader($message, 'From'),
            'body' => $this->getPlainTextBody($message->getPayload()),
        ];
    }

    public function markAsRead(string $messageId): void
    {
        $this->gmail->users_messages->modify('me', $messageId, new Gmail\ModifyMessageRequest([
            'removeLabelIds' => ['UNREAD'],
        ]));
    }

    private function getHeader(Message $message, string $name): string
    {
        $headers = $message->getPayload()->getHeaders();

        foreach ($headers as $header) {
            if (strtolower($header->getName()) === strtolower($name)) {
                return $header->getValue();
            }
        }

        return '';
    }

    private function getPlainTextBody(Gmail\MessagePart $part): string
    {
        if ($part->getMimeType() === 'text/plain') {
            $data = $part->getBody()->getData();
            $data = strtr($data, ['-' => '+', '_' => '/']);

            return quoted_printable_decode(base64_decode($data));
        }

        if ($part->getMimeType() === 'text/html') {
            $data = $part->getBody()->getData();
            $data = strtr($data, ['-' => '+', '_' => '/']);
            $html = quoted_printable_decode(base64_decode($data));

            return strip_tags($html);
        }

        if ($parts = $part->getParts()) {
            foreach ($parts as $subPart) {
                $text = $this->getPlainTextBody($subPart);

                if ($text !== '') {
                    return $text;
                }
            }
        }

        return '';
    }
}
