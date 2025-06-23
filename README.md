📬 Email Campaign & Automation Module

A complete email marketing solution for Laravel + Filament, with campaign management, automated sequences, delivery tracking, and a sleek admin interface.

📋 Overview
This module includes:

📣 Campaign creation and scheduling

🔁 Multi-step email sequences

📬 Delivery status tracking

⚙️ Background job processing

🧑‍💼 Filament-powered admin UI

🔧 Installation Guide

1. Create Database Migrations
   php artisan make:migration create_email_campaigns_table
   php artisan make:migration create_email_sequences_table
   php artisan make:migration create_email_recipients_table
   Copy the migration code from your artifacts into the generated files, then run:
   php artisan migrate

2. Create Models
   php artisan make:model EmailCampaign
   php artisan make:model EmailSequence
   php artisan make:model EmailRecipient
   Paste code from the Eloquent Models artifact.

3. Create Mailable & Template
   php artisan make:mail CampaignEmail
   mkdir -p resources/views/emails
   touch resources/views/emails/campaign.blade.php
   Copy the Mailable and Blade template from your artifacts.

4. Create Jobs
   php artisan make:job SendScheduledEmails
   php artisan make:job ProcessCampaignQueue
   php artisan make:job SendTestEmail
   Insert the code from the Scheduled Jobs artifact.

5. Update Filament Resource
   Replace EmailCampaignResource.php with the version from the Enhanced Filament Resource artifact.

6. Create Filament Pages
   php artisan make:filament-page ViewCampaignRecipients --resource=EmailCampaignResource
   Paste code from the Filament Resource Pages artifact.

7. Create Relation Manager
   php artisan make:filament-relation-manager EmailCampaignResource recipients
   Use the code from the Relation Manager section.

8. Create View File
   mkdir -p resources/views/filament/resources/email-campaign-resource/pages
   touch resources/views/filament/resources/email-campaign-resource/pages/view-campaign-recipients.blade.php

9. Create Console Commands
   Schedule::command('email:process-queue')->everyFiveMinutes()->runInBackground()->withoutOverlapping();
   Schedule::command('email:stats')->hourly();
   Paste code from the Console Commands artifact and register them in routes/console.php.

10. Configure Environment
    Add to your .env file:
    EMAIL_CAMPAIGN_BATCH_SIZE=100
    EMAIL_CAMPAIGN_DELAY=60
    EMAIL_CAMPAIGN_MAX_RETRIES=3
    QUEUE_CONNECTION=database

11. Setup Queue System
    php artisan queue:table
    php artisan migrate
    🚀 Usage Guide
    🎯 Creating Email Campaigns
    Go to Email Campaigns in the Filament admin panel.

Click Create Campaign.

Fill in:

Title – Internal reference name

Subject – Email subject

Target Segment – Audience filter

Content – Rich text email body

Supports tokens like {{user_name}}, {{first_name}}, {{user_email}}

➕ Add Email Sequences
Click Add Sequence Step

Set delay days (e.g., 3 = 3 days after main email)

Add subject and content for each step

🧪 Test Campaign
Use Send Test to preview and verify email formatting and tokens

📅 Schedule or Send
Choose schedule or click Send Now for immediate delivery

👥 Managing Recipients
View Recipients – From the campaign list

Track Status – See sent, failed, or pending

Resend Failed – Retry sending with one click

Export – Download recipient data from table

📊 Monitoring Performance
Dashboard Stats – Total recipients, delivery progress

Status Tracking – Draft, Scheduled, Sending, Sent

Error Logs – View failed delivery reasons

✨ Features
✅ Campaign Management
Rich text editor

Scheduling support

Segmentation

✅ Email Sequences
Multi-step automation

Custom delay configuration

✅ Delivery Tracking
Per-user delivery status

Error handling and retries

✅ Admin Interface (Filament)
Full CRUD operations

Filtering, exporting, test emails

✅ Background Processing
Queue-powered processing

Batch sending & retries

Laravel scheduler integration

🔄 Automated Processing
Every 15 mins: Laravel scheduler triggers jobs

Batch sending: Based on config (EMAIL_CAMPAIGN_BATCH_SIZE)

Retries & Logs: Tracks failures and allows retry

Status updates: Campaigns move through lifecycle stages automatically

🛠️ Customization Options
🎯 Segmentation
Modify getTargetUsers() in ProcessCampaignQueue.php:

case 'custom_segment':
$query->where('custom_field', 'custom_value');
break;
💌 Email Templates
Update resources/views/emails/campaign.blade.php to match your brand.

🧬 Personalization
Extend getEmailContent() in CampaignEmail.php to support more tokens.

🔍 Troubleshooting
Common Issues
Problem Solution
Emails not sending Ensure php artisan queue:work is running
Test emails failing Verify mail config in .env
Recipients not created Check segment logic in ProcessCampaignQueue.php
Scheduler not working Make sure Laravel cron job is set up

Useful Commands

# View campaign stats

php artisan email:stats

# Manually process queue

php artisan email:process-queue

# Monitor queue

php artisan queue:monitor

# Clear failed jobs

php artisan queue:flush
📦 License
MIT – feel free to use, extend, and contribute.
