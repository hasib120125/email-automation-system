<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_campaign_id',
        'email_sequence_id',
        'user_id',
        'status',
        'sent_at',
        'scheduled_for',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequence::class, 'email_sequence_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function isReadyToSend(): bool
    {
        return $this->status === 'pending' && 
               $this->scheduled_for && 
               $this->scheduled_for->isPast();
    }
}
