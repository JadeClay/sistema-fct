<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Collection;

class GmailService
{
    private Client $client;

    private Gmail $gmail;

    public function __construct()
    {
        $this->client = new Client;
        $this->client->setAuthConfig(config('gmail.auth_config'));
        $this->client->setSubject(config('gmail.impersonated_user'));
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
