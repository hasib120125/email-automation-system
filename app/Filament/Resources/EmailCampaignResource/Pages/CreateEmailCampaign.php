<?php

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Resources\EmailCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailCampaign extends CreateRecord
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set status based on scheduled_at
        if (isset($data['scheduled_at']) && $data['scheduled_at']) {
            $data['status'] = 'scheduled';
        } else {
            $data['status'] = 'draft';
        }

        return $data;
    }
}
