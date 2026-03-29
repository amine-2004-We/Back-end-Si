<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\ProjectStatus;

/**
 * Class ProjectObserver
 */
class ProjectObserver
{
    /**
     * @param Project $project
     */
    public function creating(Project $project): void
    {
        $this->castProjectAbbreviation($project);
        if (!$project->project_status_id) {
             $brouillonStatus = ProjectStatus::where('name', 'Brouillon')->first();
            if ($brouillonStatus) {
                $project->project_status_id = $brouillonStatus->id;
            }
       }



        if (empty($project->current_phase)) {
           $project->current_phase = Project::PHASE_PRE_PROJECT;
       }
    }

    /**
     * Handle the Project "updating" event.
     */
    public function updating(Project $project): void
    {
        if ($project->isDirty('project_name') || $project->isDirty('project_abbreviation')) {
            $this->castProjectAbbreviation($project);
             $this->generateProjectCode($project);
        }
    }

    public function created(Project $project): void
    {
        $this->generateProjectCode($project);
        $this->assignTeamBasedOnAxisAndRegion($project);
        $project->saveQuietly();
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        //
    }

    /**
     * @param Project $project
     * @return void
     * Generate project code with format: Partenaire-Abreviationprojet-année
     */
    private function generateProjectCode(Project $project): void
    {
        $year = $project->created_at ? $project->created_at->format('Y') : date('Y');
        $projectAbbr = strtoupper($project->project_abbreviation ?? 'PROJ');

        $partnerAbbr = 'ZKR';

        if ($project->partner_id) {
            if ($project->relationLoaded('associatedPartner')) {
                $partnerAbbr = strtoupper($project->associatedPartner->abbreviation);
            } else {
                $partner = \App\Models\Partner::find($project->partner_id);
                if ($partner) {
                    $partnerAbbr = strtoupper($partner->abbreviation);
                }
            }
        }
        else {
            $principalPartner = $project->partners()
                ->wherePivot('partner_role', 'principal')
                ->first();

            if ($principalPartner) {
                $partnerAbbr = strtoupper($principalPartner->abbreviation);
            }
        }

        $project->project_code = "{$projectAbbr}/{$partnerAbbr}/{$year}";
    }

    private function castProjectAbbreviation(Project $project): void
    {
        if ($project->project_abbreviation) {
            $project->project_abbreviation = strtoupper($project->project_abbreviation);
        }
    }

     /**
     * Affectation automatique d'équipe selon Axe et Région.
     */
    private function assignTeamBasedOnAxisAndRegion(Project $project): void
    {
        if (!$project->intervention_axis_id || !$project->region) {
            return;
        }

        // Logique fictive de récupération des collaborateurs basés sur la matrice Axe/Région
        // Dans une application réelle, cela viendrait d'une table de configuration 'TeamAssignmentRule'

        /*
        $collaborators = Collaborator::whereHas('assignments', function($q) use ($project) {
            $q->where('axis_id', $project->intervention_axis_id)
              ->where('region', $project->region);
        })->get();

        if ($collaborators->isNotEmpty()) {
            $project->collaborators()->syncWithoutDetaching($collaborators->pluck('id'));

            // Notification automatique
            // foreach($collaborators as $collab) {
            //    Mail::to($collab->email)->send(new ProjectAssignedNotification($project));
            // }
        }
        */
    }
}
