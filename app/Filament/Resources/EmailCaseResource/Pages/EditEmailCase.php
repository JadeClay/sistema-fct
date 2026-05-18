<?php

namespace App\Filament\Resources\EmailCaseResource\Pages;

use App\Filament\Resources\EmailCaseResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditEmailCase extends EditRecord
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
            DeleteAction::make(),
        ];
    }
}
