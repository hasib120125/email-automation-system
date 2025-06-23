<?php

namespace App\Jobs;

use App\Mail\CampaignEmail;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public string $testEmail
    ) {}

    public function handle(): void
    {
        // Create a mock user for testing
        $testUser = new User([
            'name' => 'Test User',
            'email' => $this->testEmail,
            'first_name' => 'Test',
        ]);

        $mailable = new CampaignEmail($this->campaign, $testUser);
        
        Mail::to($this->testEmail)->send($mailable);
    }
}