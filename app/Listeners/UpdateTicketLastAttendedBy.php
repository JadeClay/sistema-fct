<?php

namespace App\Listeners;

use App\Models\Ticket;
use Relaticle\Comments\Events\CommentCreated;

class UpdateTicketLastAttendedBy
{
    public function handle(CommentCreated $event): void
    {
        $commentable = $event->comment->commentable;

        if ($commentable instanceof Ticket) {
            $commentable->update([
                'last_attended_by' => $event->comment->commenter->getKey(),
            ]);
        }
    }
}
