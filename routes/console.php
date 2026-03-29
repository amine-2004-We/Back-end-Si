<?php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('conventions:send-reporting-notifications')
    ->dailyAt('05:00');
Schedule::command('tasks:check')->dailyAt('05:00');
Schedule::command('convention:expiry-alert')->dailyAt('05:00');
Schedule::command('financial:installment-alert')->dailyAt('05:00');

