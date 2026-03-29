<?php

namespace App\Console\Commands;

use App\Models\Training;
use App\Services\Notification\MailService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTrainingAlerts extends Command
{
    protected $signature = 'training:send-alerts';
    protected $description = 'Envoie une alerte si une formation n\'est pas validée 48h après sa fin';

    public function handle()
    {
        MailService::sendMail('kevini9797@mustaer.com',
            'Bienvenue dans le projet',
            'emails.notification',
            [
                'title' => 'Bienvenue',
                'content' => 'Merci de rejoindre le projet Laravel Fondation Zakoura.',
                'subject' => 'Onboarding'
            ]);
        dd('stop');
        $threshold = Carbon::now()->subHours(48);

        $trainings = Training::with('responsible', 'cabinet')
            ->where('status', 'completed')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $threshold)
            ->get();

        MailService::sendMail('kevini9797@mustaer.com',
            'Bienvenue dans le projet',
            'emails.notification',
            [
                'title' => 'Bienvenue',
                'content' => 'Merci de rejoindre le projet Laravel Fondation Zakoura.',
                'subject' => 'Onboarding'
            ]);
        dd('stop');
        foreach ($trainings as $training) {
            $messageText = "Pas de validation finale 48h après la fin de la formation.";
            MailService::sendMail($training->responsible->email, $messageText, "emails.training_alert",);
            $this->info("Alerte envoyée pour la formation: {$training->title}");
        }

        return Command::SUCCESS;
    }
}
