<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\Notification\MailService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckTasksCommand extends Command
{
    protected $signature = 'tasks:check';
    protected $description = 'Vérifie les tâches des projets et envoie les notifications';

    public function handle()
    {
        $projects = Project::all();
        $today = Carbon::today()->startOfDay();; // aujourd'hui à 00:00

        foreach ($projects as $project) {

            // Vérifie qu'il y a des tasks
            if ($project->tasks->isEmpty()) {
                continue;
            }

            foreach ($project->tasks as $task) {

                // --- Notification J-3 au responsable de la tâche ---
                if (!empty($task->expected_start_date)) {
                    $alertDate = $task->expected_start_date->copy()->subDays(3)->startOfDay();

                    if ($alertDate->isSameDay($today) && $task->status !== 'En cours') {
                        $responsableEmail = $task->responsibleCollaborator?->email ?? null;
                        if($responsableEmail){
                          MailService::sendMailRaw(
                                $responsableEmail,
                                "Rappel : Notification tâche à venir",
                                "La tâche avec le ID #{$task->id} et le titre : ({$task->title}) commence dans 3 jours ({$task->expected_start_date->format('Y-m-d')})."
                            );
                            $this->info("Notification J-3 envoyée à {$responsableEmail} pour la tâche #{$task->id}");
                        }


                    }
                }

                // --- Notification au responsable du projet si la tâche est en retard ---
                if (!empty($task->expected_end_date)) {
                    $expectedEnd = $task->expected_end_date->copy()->startOfDay();
                    if ($today->gt($expectedEnd) && $task->status !== 'Réalisée') {
                        $responsableProjetEmail = $project->responsible?->email ?? null;
                        if($responsableProjetEmail) {
                           MailService::sendMailRaw(
                                $responsableProjetEmail,
                                "Notification Retard sur une tâche du projet",
                                "La tâche #{$task->id}  et le titre : ({$task->title}) est en retard depuis le {$expectedEnd->format('Y-m-d')}."
                            );

                            $this->info("Notification de retard envoyée à {$responsableProjetEmail} pour la tâche #{$task->id}");
                        }

                    }
                }
            }
        }
        $this->info("Vérification des tâches effectuée.");
    }
}
