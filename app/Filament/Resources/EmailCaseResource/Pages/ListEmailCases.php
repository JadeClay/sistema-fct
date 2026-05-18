<?php

namespace App\Filament\Resources\EmailCaseResource\Pages;

use App\Filament\Resources\EmailCaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailCases extends ListRecords
{
    protected static string $resource = EmailCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
