<?php

namespace App\Console\Commands;

use App\Models\FinancialInstallment;
use App\Services\Notification\MailService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class FinancialInstallmentAlertCommand extends Command
{
    protected $signature = 'financial:installment-alert';
    protected $description = 'Gère les notifications des échéances des tranches financières (installments)';

    public function handle()
    {
        $today = Carbon::today();

        $installments = FinancialInstallment::with([
            'convention.project.responsible'
        ])->get();

        foreach ($installments as $installment) {
            $dueDate = $installment->due_date;

            // --- Destinataires depuis la convention/projet ---
            $recipientsDev = optional($installment->convention?->project?->responsableDeveloppement)->email;
            $recipientsFinance = optional($installment->convention?->project?->responsableFinance)->email;
            $recipientsProject = optional($installment->convention?->project?->responsableProjet)->email;
            $recipientDG = 'dg@example.com'; // Email Directeur Général

            // --- 1️⃣ Alerte 15 jours avant échéance ---
            if ($dueDate && $today->copy()->addDays(15)->isSameDay($dueDate)) {
                $alertRecipients = array_filter([$recipientsDev, $recipientsFinance]);

                if (!empty($alertRecipients)) {
                    MailService::sendMailRaw(
                        $alertRecipients,
                        "Alerte : tranche financière à venir",
                        "L'échéance #{$installment->installment_number} de la convention #{$installment->convention_id} arrive le {$dueDate->format('d/m/Y')}."
                    );
                    $this->info("Alerte 15 jours envoyée pour échéance #{$installment->installment_number}");
                } else {
                    $this->warn("Aucune adresse email pour l'alerte 15 jours de l'échéance #{$installment->installment_number}");
                }
            }

            // --- 2️⃣ Notification retard à la date prévue si non reçue ---
            if ($dueDate && $today->isSameDay($dueDate) && (!$installment->amount_received || $installment->amount_received == 0)) {
                $alertRecipients = array_filter([$recipientsDev, $recipientsFinance, $recipientDG]);

                if (!empty($alertRecipients)) {
                    MailService::sendMailRaw(
                        $alertRecipients,
                        "Notification retard sur tranche financière",
                        "L'échéance #{$installment->installment_number} prévue le {$dueDate->format('d/m/Y')} n'a pas encore été reçue."
                    );
                    $this->info("Notification retard envoyée pour échéance #{$installment->installment_number}");
                } else {
                    $this->warn("Aucune adresse email pour la notification retard de l'échéance #{$installment->installment_number}");
                }
            }

            // --- 3️⃣ Notification tranche reçue ---
            if ($installment->amount_received && $installment->status !== 'Reçue') {
                $installment->status = 'Reçue';
                $installment->reception_date = $today;
                $installment->save();

                $alertRecipients = array_filter([$recipientsProject, $recipientsDev, $recipientsFinance, $recipientDG]);

                if (!empty($alertRecipients)) {
                    MailService::sendMailRaw(
                        $alertRecipients,
                        "Tranche reçue",
                        "L'échéance #{$installment->installment_number} de la convention #{$installment->convention_id} a été reçue le {$today->format('d/m/Y')}."
                    );
                    $this->info("Notification réception envoyée pour échéance #{$installment->installment_number}");
                } else {
                    $this->warn("Aucune adresse email pour la notification réception de l'échéance #{$installment->installment_number}");
                }
            }
        }

        $this->info("Vérification des échéances terminée.");
    }
}
