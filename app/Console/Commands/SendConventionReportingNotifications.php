<?php

namespace App\Console\Commands;

use App\Models\Convention;
use App\Models\Collaborator;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendConventionReportingNotifications extends Command
{
    protected $signature = 'conventions:send-reporting-notifications';
    protected $description = 'Send reporting notifications for conventions based on periodicity';

    public function handle()
    {
        Log::info('Running conventions:send-reporting-notifications');

        $today = Carbon::today()->toDateString();

        $conventions = Convention::whereDate('reporting_next_date', $today)->get();

        if ($conventions->isEmpty()) {
            Log::info("No conventions need reporting today ({$today})");
            return Command::SUCCESS;
        }

        foreach ($conventions as $convention) {
            try {
                $creator = $convention->creator;

                if (!$creator || !$creator->collaborator) {
                    Log::warning("Convention ID {$convention->id} has no creator or collaborator");
                    continue;
                }

                $senderId = $creator->collaborator->id;

                $title = "Reporting à préparer";
                $text = "Le reporting de la convention '{$convention->title}' arrive à échéance.";

                $target = [
                    'type' => 'redirect',
                    'name' => 'view_convention',
                    'id' => $convention->id,
                ];

                $recipientIds = [$convention->responsible_id];

                $notification = app(\App\Services\NotificationService::class)->save(
                    $title,
                    $text,
                    $senderId,
                    $target,
                    recipients: $recipientIds
                );

                Log::info("Notification sent for convention ID {$convention->id}", [
                    'notification_id' => $notification->id,
                    'recipients' => $recipientIds,
                ]);

            } catch (\Throwable $e) {
                Log::error('Error sending notification: ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

}
