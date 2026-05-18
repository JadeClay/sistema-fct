<?php

namespace App\Console\Commands;

use App\Models\EmailCase;
use App\Services\GmailService;
use Illuminate\Console\Command;

class FetchEmails extends Command
{
    protected $signature = 'fetch:emails';

    protected $description = 'Fetch unread emails from Gmail and create EmailCase records';

    public function handle(): int
    {
        if (config('gmail.auth_mode') === 'oauth') {
            if (! config('gmail.client_id') || ! config('gmail.client_secret')) {
                $this->error('Gmail OAuth is not configured. Set GMAIL_CLIENT_ID and GMAIL_CLIENT_SECRET in .env');

                return self::FAILURE;
            }
        } elseif (! config('gmail.auth_config') || ! config('gmail.impersonated_user')) {
            $this->error('Gmail API is not configured. Set GMAIL_AUTH_CONFIG and GMAIL_IMPERSONATED_USER in .env');

            return self::FAILURE;
        }

        $gmail = app(GmailService::class);
        $messages = $gmail->getUnreadMessages();

        if ($messages->isEmpty()) {
            $this->info('No unread emails found.');

            return self::SUCCESS;
        }

        $this->info("Found {$messages->count()} unread email(s).");

        $created = 0;

        foreach ($messages as $message) {
            try {
                $detail = $gmail->getMessageDetail($message->getId());

                EmailCase::firstOrCreate(
                    ['gmail_message_id' => $message->getId()],
                    [
                        'subject' => $detail['subject'],
                        'sender_email' => $detail['sender'],
                        'body' => $detail['body'],
                        'gmail_message_id' => $message->getId(),
                        'is_resolved' => false,
                    ],
                );

                $gmail->markAsRead($message->getId());

                $this->line("  Imported: {$detail['subject']}");

                $created++;
            } catch (\Throwable $th) {
                $this->warn("  Failed to import message {$message->getId()}: {$th->getMessage()}");
            }
        }

        $this->info("Created {$created} EmailCase record(s).");

        return self::SUCCESS;
    }
}
