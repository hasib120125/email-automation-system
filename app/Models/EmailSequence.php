<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_campaign_id',
        'delay_days',
        'subject',
        'body',
        'step_order',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class);
    }

    public function getScheduledDateForCampaign(EmailCampaign $campaign): \Carbon\Carbon
    {
        return $campaign->scheduled_at->addDays($this->delay_days);
    }
}
