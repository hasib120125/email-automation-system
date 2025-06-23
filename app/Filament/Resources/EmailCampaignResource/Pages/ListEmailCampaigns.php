<?php

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Resources\EmailCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmailCampaigns extends ListRecords
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Campaign')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Campaigns'),
            'draft' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(fn () => \App\Models\EmailCampaign::where('status', 'draft')->count()),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'scheduled'))
                ->badge(fn () => \App\Models\EmailCampaign::where('status', 'scheduled')->count()),
            'sending' => Tab::make('Sending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'sending'))
                ->badge(fn () => \App\Models\EmailCampaign::where('status', 'sending')->count()),
            'sent' => Tab::make('Sent')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'sent'))
                ->badge(fn () => \App\Models\EmailCampaign::where('status', 'sent')->count()),
        ];
    }
}
