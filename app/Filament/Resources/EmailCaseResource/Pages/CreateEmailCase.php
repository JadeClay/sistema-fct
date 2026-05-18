<?php

namespace App\Filament\Resources\EmailCaseResource\Pages;

use App\Filament\Resources\EmailCaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailCase extends CreateRecord
{
    protected static string $resource = EmailCaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->hasRole('Agent')) {
            $data['assigned_to'] = auth()->id();
        }

        return $data;
    }
}
