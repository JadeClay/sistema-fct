<?php

namespace App\Filament\Resources\EmailCaseResource\Pages;

use App\Filament\Resources\EmailCaseResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewEmailCase extends ViewRecord
{
    protected static string $resource = EmailCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('replyInGmail')
                ->label('Reply in Gmail')
                ->icon(Heroicon::OutlinedLink)
                ->url(fn (): string => 'https://mail.google.com/mail/u/0/#search/rfc822msgid:'.$this->record->gmail_message_id)
                ->openUrlInNewTab()
                ->color('success'),
            Action::make('convertToTicket')
                ->label('Convert to Ticket')
                ->icon(Heroicon::OutlinedArrowRightOnRectangle)
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->options(fn (): array => \App\Models\Category::pluck('name', 'id')->toArray())
                        ->required(),
                    \Filament\Forms\Components\Select::make('status_id')
                        ->label('Status')
                        ->options(fn (): array => \App\Models\Status::pluck('name', 'id')->toArray())
                        ->required(),
                    \Filament\Forms\Components\Select::make('assigned_to')
                        ->label('Assigned to')
                        ->options(fn (): array => \App\Models\User::role(['Super Admin', 'Supervisor', 'Agent'])->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->nullable()
                        ->hidden(fn (): bool => auth()->user()?->hasRole('Agent')),
                ])
                ->action(function (array $data): void {
                    $ticket = \App\Models\Ticket::create([
                        'title' => $this->record->subject,
                        'description' => $this->record->body,
                        'category_id' => $data['category_id'],
                        'status_id' => $data['status_id'],
                        'assigned_to' => $data['assigned_to'],
                        'creator_id' => auth()->id(),
                    ]);

                    $this->record->update(['is_resolved' => true]);

                    \Filament\Notifications\Notification::make()
                        ->title('Ticket created successfully')
                        ->success()
                        ->send();

                    $this->redirect(\App\Filament\Resources\TicketResource::getUrl('view', ['record' => $ticket]));
                }),
            DeleteAction::make(),
        ];
    }
}
