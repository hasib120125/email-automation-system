<?php

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ViewCampaignRecipients extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = EmailCampaignResource::class;

    protected static string $view = 'filament.resources.email-campaign-resource.pages.view-campaign-recipients';

    public EmailCampaign $record;

    public function mount(EmailCampaign $record): void
    {
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EmailRecipient::query()
                    ->where('email_campaign_id', $this->record->id)
                    ->with(['user', 'sequence'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recipient Name')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email Address')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('sequence.subject')
                    ->label('Email Type')
                    ->formatStateUsing(function ($state, EmailRecipient $record) {
                        if ($record->sequence) {
                            return "Sequence: Day {$record->sequence->delay_days}";
                        }
                        return 'Main Campaign';
                    })
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'sent',
                        'danger' => 'failed',
                        'secondary' => 'bounced',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('scheduled_for')
                    ->label('Scheduled For')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(50)
                    ->tooltip(function (EmailRecipient $record): ?string {
                        return $record->error_message;
                    })
                    ->visible(fn (): bool => $this->record->failed_count > 0),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                        'bounced' => 'Bounced',
                    ]),
                
                Tables\Filters\Filter::make('sequence_type')
                    ->form([
                        \Filament\Forms\Components\Select::make('type')
                            ->options([
                                'main' => 'Main Campaign',
                                'sequence' => 'Sequence Emails',
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['type'] === 'main',
                            fn (Builder $query) => $query->whereNull('email_sequence_id')
                        )->when(
                            $data['type'] === 'sequence',
                            fn (Builder $query) => $query->whereNotNull('email_sequence_id')
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (EmailRecipient $record) {
                        $record->update([
                            'status' => 'pending',
                            'error_message' => null,
                            'scheduled_for' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Email rescheduled')
                            ->body('The email has been rescheduled for immediate sending.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (EmailRecipient $record): bool => $record->status === 'failed'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public function getTitle(): string
    {
        return "Recipients: {$this->record->title}";
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Back to Campaigns')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }
}