<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subject',
        'content',
        'scheduled_at',
        'target_segment',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function sequences(): HasMany
    {
        return $this->hasMany(EmailSequence::class)->orderBy('delay_days');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }
        return round(($this->sent_count / $this->total_recipients) * 100, 2);
    }

    public function canBeSent(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']) && 
               $this->scheduled_at && 
               $this->scheduled_at->isPast();
    }

    public function markAsScheduled(): void
    {
        $this->update(['status' => 'scheduled']);
    }

    public function markAsSending(): void
    {
        $this->update(['status' => 'sending']);
    }

    public function markAsSent(): void
    {
        $this->update(['status' => 'sent']);
    }

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailedCount(): void
    {
        $this->increment('failed_count');
    }
}
