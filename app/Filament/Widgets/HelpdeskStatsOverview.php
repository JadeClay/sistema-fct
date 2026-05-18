<?php

namespace App\Filament\Widgets;

use App\Models\EmailCase;
use App\Models\Status;
use App\Models\Ticket;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class HelpdeskStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Helpdesk Overview';

    protected function getStats(): array
    {
        $user = Auth::user();
        $resolvedStatusIds = Status::where('is_resolved', true)->pluck('id');

        $ticketQuery = Ticket::query();
        $emailCaseQuery = EmailCase::query();

        if ($user?->hasRole('Agent')) {
            $ticketQuery->where('assigned_to', $user->id);
            $emailCaseQuery->where('assigned_to', $user->id);
        }

        $openTicketsCount = (clone $ticketQuery)
            ->whereNotIn('status_id', $resolvedStatusIds)
            ->count();

        $resolvedTicketsCount = (clone $ticketQuery)
            ->whereIn('status_id', $resolvedStatusIds)
            ->count();

        $pendingEmailCasesCount = (clone $emailCaseQuery)
            ->where('is_resolved', false)
            ->count();

        return [
            Stat::make('Open Tickets', $openTicketsCount)
                ->description('Awaiting resolution')
                ->descriptionIcon(Heroicon::OutlinedTicket)
                ->color('warning'),
            Stat::make('Resolved Tickets', $resolvedTicketsCount)
                ->description('Completed or closed')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
            Stat::make('Pending Email Cases', $pendingEmailCasesCount)
                ->description('Not yet converted')
                ->descriptionIcon(Heroicon::OutlinedInbox)
                ->color('danger'),
        ];
    }
}
