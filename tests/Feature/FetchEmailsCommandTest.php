<?php

use App\Console\Commands\FetchEmails;
use App\Models\EmailCase;
use App\Services\GmailService;
use Google\Service\Gmail\Message;

use function Pest\Laravel\mock;

beforeEach(function () {
    config()->set('gmail.auth_config', storage_path('app/gmail/test-credentials.json'));
    config()->set('gmail.impersonated_user', 'test@example.com');
});

it('shows error when gmail is not configured', function () {
    config()->set('gmail.auth_config', null);

    $this->artisan(FetchEmails::class)
        ->assertExitCode(1)
        ->expectsOutput('Gmail API is not configured. Set GMAIL_AUTH_CONFIG and GMAIL_IMPERSONATED_USER in .env');
});

it('shows message when no unread emails', function () {
    $mock = mock(GmailService::class);
    $mock->shouldReceive('getUnreadMessages')->once()->andReturn(collect());

    $this->artisan(FetchEmails::class)
        ->assertExitCode(0)
        ->expectsOutput('No unread emails found.');
});

it('fetches unread emails and creates email case records', function () {
    $messageId = 'abc123';

    $message = new Message;
    $message->setId($messageId);

    $detail = [
        'subject' => 'Test Subject',
        'sender' => 'sender@example.com',
        'body' => 'Test email body content',
    ];

    $mock = mock(GmailService::class);
    $mock->shouldReceive('getUnreadMessages')->once()->andReturn(collect([$message]));
    $mock->shouldReceive('getMessageDetail')->with($messageId)->once()->andReturn($detail);
    $mock->shouldReceive('markAsRead')->with($messageId)->once();

    $this->artisan(FetchEmails::class)
        ->assertExitCode(0)
        ->expectsOutputToContain('Found 1 unread email(s)')
        ->expectsOutputToContain('Imported: Test Subject')
        ->expectsOutputToContain('Created 1 EmailCase record(s).');

    $this->assertDatabaseHas('email_cases', [
        'gmail_message_id' => $messageId,
        'subject' => 'Test Subject',
        'sender_email' => 'sender@example.com',
        'body' => 'Test email body content',
        'is_resolved' => false,
    ]);
});

it('skips duplicate messages gracefully', function () {
    EmailCase::factory()->create([
        'gmail_message_id' => 'dup123',
        'subject' => 'Existing',
        'sender_email' => 'existing@example.com',
        'gmail_message_id' => 'dup123',
    ]);

    $message = new Message;
    $message->setId('dup123');

    $detail = [
        'subject' => 'Existing',
        'sender' => 'existing@example.com',
        'body' => 'Existing content',
    ];

    $mock = mock(GmailService::class);
    $mock->shouldReceive('getUnreadMessages')->once()->andReturn(collect([$message]));
    $mock->shouldReceive('getMessageDetail')->with('dup123')->once()->andReturn($detail);
    $mock->shouldReceive('markAsRead')->with('dup123')->once();

    $this->artisan(FetchEmails::class)
        ->assertExitCode(0);

    expect(EmailCase::where('gmail_message_id', 'dup123')->count())->toBe(1);
});
