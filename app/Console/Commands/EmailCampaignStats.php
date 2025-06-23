<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use Illuminate\Console\Command;

class EmailCampaignStats extends Command
{
    protected $signature = 'email:stats {campaign_id?}';
    
    protected $description = 'Show email campaign statistics';

    public function handle()
    {
        $campaignId = $this->argument('campaign_id');
        
        if ($campaignId) {
            $this->showCampaignStats($campaignId);
        } else {
            $this->showOverallStats();
        }
        
        return 0;
    }
    
    private function showCampaignStats($campaignId)
    {
        $campaign = EmailCampaign::with(['recipients', 'sequences'])->find($campaignId);
        
        if (!$campaign) {
            $this->error('Campaign not found.');
            return;
        }
        
        $this->info("Campaign: {$campaign->title}");
        $this->info("Status: {$campaign->status}");
        $this->info("Scheduled: " . ($campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d H:i:s') : 'Not scheduled'));
        $this->info("Total Recipients: {$campaign->total_recipients}");
        $this->info("Sent: {$campaign->sent_count}");
        $this->info("Failed: {$campaign->failed_count}");
        $this->info("Progress: {$campaign->progress_percentage}%");
        $this->info("Sequences: " . $campaign->sequences->count());
    }
    
    private function showOverallStats()
    {
        $this->info('Overall Email Campaign Statistics');
        $this->line('================================');
        
        $campaigns = EmailCampaign::selectRaw('
            status,
            COUNT(*) as count,
            SUM(total_recipients) as total_recipients,
            SUM(sent_count) as sent_count,
            SUM(failed_count) as failed_count
        ')->groupBy('status')->get();
        
        $this->table(
            ['Status', 'Campaigns', 'Total Recipients', 'Sent', 'Failed'],
            $campaigns->map(function ($campaign) {
                return [
                    ucfirst($campaign->status),
                    $campaign->count,
                    number_format($campaign->total_recipients),
                    number_format($campaign->sent_count),
                    number_format($campaign->failed_count),
                ];
            })->toArray()
        );
    }
}