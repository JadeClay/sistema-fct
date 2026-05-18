<?php

namespace App\Models;

use Database\Factories\EmailCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCase extends Model
{
    /** @use HasFactory<EmailCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'subject',
        'sender_email',
        'body',
        'assigned_to',
        'gmail_message_id',
        'is_resolved',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, EmailCase> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
