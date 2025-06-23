<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign
    ) {}

    public function handle(): void
    {
        if ($this->campaign->status !== 'scheduled') {
            Log::warning('Campaign is not in scheduled status', [
                'campaign_id' => $this->campaign->id,
                'status' => $this->campaign->status,
            ]);
            return;
        }

        $this->campaign->markAsSending();

        try {
            // Get target users based on segment
            $users = $this->getTargetUsers();
            
            // Create recipients for main campaign
            $this->createCampaignRecipients($users);
            
            // Create recipients for sequences
            $this->createSequenceRecipients($users);
            
            // Update campaign totals
            $this->campaign->update([
                'total_recipients' => $this->campaign->recipients()->count(),
            ]);

            Log::info('Campaign queue processed successfully', [
                'campaign_id' => $this->campaign->id,
                'total_recipients' => $this->campaign->total_recipients,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process campaign queue', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->campaign->update(['status' => 'failed']);
        }
    }

    private function getTargetUsers()
    {
        $query = User::query();

        // Apply segment filtering
        if ($this->campaign->target_segment) {
            switch ($this->campaign->target_segment) {
                case 'all_djs':
                    // Assuming you have a role or type field
                    $query->where('role', 'dj');
                    break;
                case 'active_djs':
                    $query->where('role', 'dj')
                          ->where('last_login_at', '>=', now()->subDays(30));
                    break;
                case 'new_djs':
                    $query->where('role', 'dj')
                          ->where('created_at', '>=', now()->subDays(7));
                    break;
                default:
                    $query->where('role', 'dj');
            }
        }

        return $query->get();
    }

    private function createCampaignRecipients($users): void
    {
        foreach ($users as $user) {
            EmailRecipient::create([
                'email_campaign_id' => $this->campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'scheduled_for' => $this->campaign->scheduled_at,
            ]);
        }
    }

    private function createSequenceRecipients($users): void
    {
        foreach ($this->campaign->sequences as $sequence) {
            $scheduledFor = $this->campaign->scheduled_at->addDays($sequence->delay_days);
            
            foreach ($users as $user) {
                EmailRecipient::create([
                    'email_campaign_id' => $this->campaign->id,
                    'email_sequence_id' => $sequence->id,
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'scheduled_for' => $scheduledFor,
                ]);
            }
        }
    }
}