<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public User $user,
        public ?EmailSequence $sequence = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->sequence ? $this->sequence->subject : $this->campaign->subject;
        
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.campaign',
            with: [
                'campaign' => $this->campaign,
                'user' => $this->user,
                'sequence' => $this->sequence,
                'content' => $this->getEmailContent(),
            ],
        );
    }

    private function getEmailContent(): string
    {
        $content = $this->sequence ? $this->sequence->body : $this->campaign->content;
        
        // Replace placeholders with user data
        $content = str_replace([
            '{{user_name}}',
            '{{user_email}}',
            '{{first_name}}',
        ], [
            $this->user->name,
            $this->user->email,
            $this->user->first_name ?? $this->user->name,
        ], $content);

        return $content;
    }

    public function attachments(): array
    {
        return [];
    }
}