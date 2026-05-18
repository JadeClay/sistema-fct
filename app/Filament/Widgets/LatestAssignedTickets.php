<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestAssignedTickets extends BaseTableWidget
{
    protected function getTableQuery(): Builder
    {
        $query = Ticket::query()->with('status');

        if (Auth::user()?->hasRole('Agent')) {
            $query->where('assigned_to', Auth::id());
        }

        return $query->latest()->limit(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('title')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.name')
                    ->badge()
                    ->color(fn (Ticket $record): string => $record->status?->color ?? 'gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn (Ticket $record): string => TicketResource::getUrl('view', [$record]));
    }
}
