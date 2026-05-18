<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Relaticle\Comments\Concerns\HasComments;
use Relaticle\Comments\Contracts\Commentable;

class Ticket extends Model implements Commentable
{
    use HasComments;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'status_id',
        'creator_id',
        'assigned_to',
        'last_attended_by',
    ];

    /** @return BelongsTo<Category, Ticket> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Status, Ticket> */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /** @return BelongsTo<User, Ticket> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /** @return BelongsTo<User, Ticket> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return BelongsTo<User, Ticket> */
    public function lastAttendee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_attended_by');
    }
}
