<?php

namespace App\Providers;

use App\Listeners\UpdateTicketLastAttendedBy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Relaticle\Comments\Events\CommentCreated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(CommentCreated::class, UpdateTicketLastAttendedBy::class);
    }
}
