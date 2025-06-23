<?php

namespace App\Jobs;

use App\Mail\CampaignEmail;
use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Process ready email recipients
        $recipients = EmailRecipient::with(['campaign', 'sequence', 'user'])
            ->where('status', 'pending')
            ->where('scheduled_for', '<=', now())
            ->limit(100) // Process in batches
            ->get();

        foreach ($recipients as $recipient) {
            try {
                $this->sendEmailToRecipient($recipient);
            } catch (\Exception $e) {
                Log::error('Failed to send email to recipient: ' . $recipient->id, [
                    'error' => $e->getMessage(),
                    'recipient_id' => $recipient->id,
                    'campaign_id' => $recipient->email_campaign_id,
                ]);
                
                $recipient->markAsFailed($e->getMessage());
                $recipient->campaign->incrementFailedCount();
            }
        }

        // Check for new campaigns to schedule
        $this->scheduleNewCampaigns();
    }

    private function sendEmailToRecipient(EmailRecipient $recipient): void
    {
        $mailable = new CampaignEmail(
            $recipient->campaign,
            $recipient->user,
            $recipient->sequence
        );

        Mail::to($recipient->user->email)->send($mailable);

        $recipient->markAsSent();
        $recipient->campaign->incrementSentCount();

        Log::info('Email sent successfully', [
            'recipient_id' => $recipient->id,
            'campaign_id' => $recipient->email_campaign_id,
            'user_email' => $recipient->user->email,
        ]);
    }

    private function scheduleNewCampaigns(): void
    {
        $campaigns = EmailCampaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($campaigns as $campaign) {
            ProcessCampaignQueue::dispatch($campaign);
        }
    }
}
