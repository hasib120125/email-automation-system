<?php

namespace App\Filament\Resources;


use App\Filament\Resources\EmailCampaignResource\Pages;
use App\Jobs\ProcessCampaignQueue;
use App\Jobs\SendTestEmail;
use App\Models\EmailCampaign;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Email Marketing';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Campaign Details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('subject')
                        ->required()
                        ->maxLength(255)
                        ->helperText('This will be used as the email subject line'),
                    
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label('Schedule Date & Time')
                        ->helperText('Leave empty to save as draft'),
                    
                    Forms\Components\Select::make('target_segment')
                        ->label('Target Audience')
                        ->options([
                            'all_djs' => 'All DJs',
                            'active_djs' => 'Active DJs (last 30 days)',
                            'new_djs' => 'New DJs (last 7 days)',
                            'premium_djs' => 'Premium DJs',
                        ])
                        ->helperText('Select which DJ segment to target'),
                    
                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->helperText('Use {{user_name}}, {{first_name}}, {{user_email}} for personalization')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Email Sequences')
                ->description('Create automated follow-up emails')
                ->schema([
                    Repeater::make('sequences')
                        ->relationship('sequences')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('delay_days')
                                        ->label('Days After Campaign')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1)
                                        ->helperText('Number of days after the main campaign'),
                                    
                                    Forms\Components\TextInput::make('step_order')
                                        ->label('Step Order')
                                        ->numeric()
                                        ->default(1)
                                        ->helperText('Order of this step in the sequence'),
                                ]),
                            
                            Forms\Components\TextInput::make('subject')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Subject line for this sequence email'),
                            
                            Forms\Components\RichEditor::make('body')
                                ->required()
                                ->helperText('Use {{user_name}}, {{first_name}}, {{user_email}} for personalization'),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['delay_days']) && isset($state['subject']) 
                                ? "Day {$state['delay_days']}: {$state['subject']}" 
                                : 'New Sequence Step'
                        )
                        ->addActionLabel('Add Sequence Step')
                        ->reorderableWithButtons()
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),
                
                Tables\Columns\TextColumn::make('subject')
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Scheduled'),
                
                Tables\Columns\TextColumn::make('target_segment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'all_djs' => 'All DJs',
                        'active_djs' => 'Active DJs',
                        'new_djs' => 'New DJs',
                        'premium_djs' => 'Premium DJs',
                        default => ucfirst($state),
                    }),
                
                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('Recipients')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state) : '0'),
                
                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Sent')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state) : '0'),
                
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn (?float $state): string => $state ? $state . '%' : '0%'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'scheduled',
                        'primary' => 'sending',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'sending' => 'Sending',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\SelectFilter::make('target_segment')
                    ->options([
                        'all_djs' => 'All DJs',
                        'active_djs' => 'Active DJs',
                        'new_djs' => 'New DJs',
                        'premium_djs' => 'Premium DJs',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send_test')
                    ->label('Send Test')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('test_email')
                            ->email()
                            ->required()
                            ->default(fn () => auth()->user()->email)
                            ->helperText('Enter email address to receive test email'),
                    ])
                    ->action(function (EmailCampaign $record, array $data): void {
                        SendTestEmail::dispatch($record, $data['test_email']);
                        
                        Notification::make()
                            ->title('Test email sent!')
                            ->body("Test email has been sent to {$data['test_email']}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (EmailCampaign $record): bool => in_array($record->status, ['draft', 'scheduled'])),
                
                Tables\Actions\Action::make('schedule_now')
                    ->label('Send Now')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Campaign Now?')
                    ->modalDescription('This will immediately schedule the campaign for sending. Are you sure?')
                    ->action(function (EmailCampaign $record): void {
                        $record->update([
                            'scheduled_at' => now(),
                            'status' => 'scheduled',
                        ]);
                        
                        ProcessCampaignQueue::dispatch($record);
                        
                        Notification::make()
                            ->title('Campaign scheduled!')
                            ->body('The campaign has been queued for immediate sending.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (EmailCampaign $record): bool => $record->status === 'draft'),
                
                Tables\Actions\Action::make('view_recipients')
                    ->label('View Recipients')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(fn (EmailCampaign $record): string => 
                        route('filament.admin.resources.email-campaigns.recipients', $record)
                    )
                    ->visible(fn (EmailCampaign $record): bool => $record->total_recipients > 0),
                
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (EmailCampaign $record): bool => in_array($record->status, ['draft', 'failed'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->can('delete_campaigns')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EmailCampaignResource\RelationManagers\RecipientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaigns::route('/'),
            'create' => Pages\CreateEmailCampaign::route('/create'),
            'edit' => Pages\EditEmailCampaign::route('/{record}/edit'),
            'recipients' => Pages\ViewCampaignRecipients::route('/{record}/recipients'),
        ];
    }
}
