<?php

namespace App\Console\Commands;

use App\Models\Convention;
use App\Services\Notification\MailService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ConventionExpiryAlertCommand extends Command
{
    protected $signature = 'convention:expiry-alert';
    protected $description = 'Envoie une alerte email 30 jours avant la fin des conventions';

    public function handle()
    {
        $today = Carbon::today();             // Exemple : 2025-11-21
        $alertDate = $today->copy()->addDays(30);  // Exemple : 2025-12-21

        // Charger uniquement les conventions qui expirent EXACTEMENT dans 30 jours
        $conventions = Convention::whereNotNull('estimated_end_date')
            ->whereDate('estimated_end_date', $alertDate)
            ->get();

        foreach ($conventions as $convention) {
            // a tester anass
            // Email du responsable (pour test tu laisses ton adresse)
            $responsableEmail = $convention->responsible?->email;

            // Envoi email
            MailService::sendMailRaw(
                $responsableEmail,
                "Alerte : Convention à expiration",
                "La convention '{$convention->title}' (code : {$convention->agreement_code}) arrive à expiration dans 30 jours ({$convention->estimated_end_date->format('d/m/Y')})."
            );

            $this->info("Alerte envoyée pour la convention {$convention->agreement_code}");
        }

        $this->info("Vérification des conventions effectuée.");
    }
}
