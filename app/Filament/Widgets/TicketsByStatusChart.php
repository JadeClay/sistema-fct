<?php

namespace App\Filament\Widgets;

use App\Models\Status;
use App\Models\Ticket;
use Filament\Widgets\DoughnutChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketsByStatusChart extends DoughnutChartWidget
{
    public function getHeading(): ?string
    {
        return 'Tickets by Status';
    }

    protected function getData(): array
    {
        $query = Ticket::query();

        if (Auth::user()?->hasRole('Agent')) {
            $query->where('assigned_to', Auth::id());
        }

        $records = $query
            ->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id');

        $statuses = Status::whereIn('id', $records->keys())->get()->keyBy('id');

        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach ($records as $statusId => $count) {
            $status = $statuses->get($statusId);
            $labels[] = $status?->name ?? 'Unknown';
            $data[] = $count;
            $backgroundColor[] = $status?->color ?? '#6b7280';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
