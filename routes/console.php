<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('email:process-queue')->everyFiveMinutes()->runInBackground()->withoutOverlapping();
Schedule::command('email:stats')->hourly();

// Clean up old email logs (optional)
Schedule::command('model:prune', ['--model' => 'App\\Models\\EmailRecipient'])
            ->daily()
            ->at('02:00');
