<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            // Actions\Action::make('import_djs')
            //     ->label('Import DJs')
            //     ->icon('heroicon-o-arrow-up-tray')
            //     ->color('success')
            //     ->url(route('import.djs'))
            //     ->openUrlInNewTab(false),
        ];
    }
}
