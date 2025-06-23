<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledEmails;
use Illuminate\Console\Command;

class ProcessEmailQueue extends Command
{
    protected $signature = 'email:process-queue';
    
    protected $description = 'Process scheduled emails and send them';

    public function handle()
    {
        $this->info('Processing scheduled emails...');
        
        SendScheduledEmails::dispatch();
        
        $this->info('Email processing job dispatched successfully.');
        
        return 0;
    }
}