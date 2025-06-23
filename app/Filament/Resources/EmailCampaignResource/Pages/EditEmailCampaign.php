<?php

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Resources\EmailCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailCampaign extends EditRecord
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => in_array($this->record->status, ['draft', 'failed'])),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent editing if campaign is already sent or sending
        if (in_array($this->record->status, ['sending', 'sent'])) {
            $this->halt();
            return $data;
        }

        // Update status based on scheduled_at
        if (isset($data['scheduled_at']) && $data['scheduled_at'] && $this->record->status === 'draft') {
            $data['status'] = 'scheduled';
        }

        return $data;
    }
}
