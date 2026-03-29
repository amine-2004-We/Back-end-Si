<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\Notification\MailService;
use Illuminate\Console\Command;

class CheckProjectsCommand extends Command
{
    protected $signature = 'projects:check';
    protected $description = 'Vérifie les projets et envoie les notifications de démarrage planifié';

    public function handle()
    {
        // Date actuelle du serveur au format Y-m-d
        $today = date('Y-m-d');

        // Charger tous les projets avec start_date non null
        $projects = Project::whereNotNull('start_date')->get();

        $this->info("Total projets: " . $projects->count());

        foreach ($projects as $project) {

            // Date de début du projet
            $startDate = date('Y-m-d', strtotime($project->start_date));

            // Date d'alerte J-7
            $alertDate = date('Y-m-d', strtotime($startDate . ' -7 days'));

            // Affichage pour debug
            $this->info("Projet: {$project->project_name}");
            $this->info("ID: {$project->id}");
            $this->info("Start Date: {$startDate} | Alert J-7: {$alertDate} | Today: {$today}");

            // Vérifier si l'alerte correspond à aujourd'hui
            if ($alertDate === $today) {


                $responsableEmail = $project->responsable_email ?? 'kevini9797@mustaer.com';
                if ($responsableEmail) {
                    MailService::sendMailRaw(
                        //$responsableEmail,
                        'kevini9797@mustaer.com',
                        "Alerte : Démarrage planifié d’un projet",
                        "Le projet '{$project->project_name}' (code : {$project->project_code}) démarre dans 7 jours, le {$startDate}."
                    );
                    $this->info("Notification J-7 envoyée à {$responsableEmail} pour le projet {$project->project_code}");
                }
            }
        }

        $this->info("Vérification des projets effectuée.");
    }
}
