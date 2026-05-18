<?php

use App\Console\Commands\FetchEmails;
use Illuminate\Support\Facades\Schedule;

Schedule::command(FetchEmails::class)->everyFiveMinutes();
