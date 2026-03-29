<?php

namespace App\Repositories;

use App\Models\BudgetCategory;
use App\Models\BudgetLine;
use App\Models\Project;
use App\Models\ProjectBankAccount;
use App\Models\ProjectPartner;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProjectRepository
{
    public function all(): Builder
    {
        return Project::query();
    }

    public function allWithFilters(array $filters): Builder
    {
        $query = Project::query()
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at');

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

         if(!empty($filters['project_type_id'])) {
            $query->where('project_nature_id', '=', $filters['project_type_id']);
        }

        if(!empty($filters['project_status_id'])) {
            $query->where('project_status_id', '=', $filters['project_status_id']);
        }

        if(!empty($filters['project_nature'])) {
            $query->where('project_nature', '=', $filters['project_nature']);
        }

         if(!empty($filters['intervention_axis_id'])) {
            $query->where('intervention_axis_id', '=', $filters['intervention_axis_id']);
        }

        if(!empty($filters['region_id'])) {
            $query->where('region_id', '=', $filters['region_id']);
        }

        if(!empty($filters['province_id'])) {
            $query->where('province_id', '=', $filters['province_id']);
        }

        if(!empty($filters['project_name'])) {
            $query->where('project_name', 'ilike', '%' . $filters['project_name'] . '%');
        }

        if(!empty($filters['project_code'])) {
            $query->where('project_code', 'ilike', '%' . $filters['project_code'] . '%');
        }

        if(!empty($filters['responsible_name'])) {
            $query->whereHas('responsible', function ($q) use ($filters) {
                $q->where('name', 'ilike', '%' . $filters['responsible_name'] . '%');
            });
        }

        if(!empty($filters['partner_name'])) {
            $query->whereHas('partners', function ($q) use ($filters) {
                 $q->where('name', 'ilike', '%' . $filters['partner_name'] . '%');
            });
        }

        return $query;
    }

    public function allWithoutPagination(): Collection
    {
        return Project::all('id','project_code','project_name');
    }

    public function find(int $id): Project
    {
        return Project::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function createWithAssociations(
        array $data,
        User $createdByUser,
        ?User $responsibleUser ,
        ?ProjectBankAccount $bankAccount,
        array|null $partnersData
    ): Project {
        $project = new Project($data);
        $project->createdBy()->associate($createdByUser);
        if ($responsibleUser){
        $project->responsible()->associate($responsibleUser);
        }

        if($bankAccount) {
            $project->projectBankAccount()->associate($bankAccount);
        }

        $project->save();

        Log::info('Données reçues pour la note :', ['note' => $data['note'] ?? 'VIDE']);

         if (!empty($data['note'])) {
             $project->notes()->create([
                'user_id' => $createdByUser->id,
                'content' => $data['note']
            ]);
        }

         $formattedPartners = [];
        if($partnersData) {
            foreach ($partnersData as $partner) {
                $formattedPartners[$partner['partner_id']] = [
                    'partner_role' => $partner['partner_role'],
                    'partner_contribution' => $partner['partner_contribution'] ?? 0,
                ];
            }
        }

         if (!empty($data['partner_id'])) {
            $sourcePartnerId = $data['partner_id'];
            if (!isset($formattedPartners[$sourcePartnerId])) {
                $formattedPartners[$sourcePartnerId] = [
                    'partner_role' => ProjectPartner::ROLE_PRINCIPAL,
                    'partner_contribution' => $data['partner_amount'] ?? 0,
                ];
            } else {
                $formattedPartners[$sourcePartnerId]['partner_role'] = ProjectPartner::ROLE_PRINCIPAL;
            }
        }

        if(!empty($formattedPartners)) {
            $project->partners()->sync($formattedPartners);
        }

        return $project;
    }

    public function update(
        int $id,
        array $data,
        ?User $responsibleUser,
        ?ProjectBankAccount $bankAccount,
        ?array $partners
    ): Project {
        try {
            $project = Project::findOrFail($id);
            $project->update($data);

             if (!empty($data['note'])) {
                 $userId = auth()->id()?? ($responsibleUser ? $responsibleUser->id : $project->responsible_id);

                $project->notes()->create([
                    'user_id' => $userId,
                    'content' => $data['note']
                ]);
            }

             if (!is_null($partners)) {
                $syncData = [];
                foreach ($partners as $partner) {
                    if (!empty($partner['partner_id'])) {
                        $syncData[$partner['partner_id']] = [
                            'partner_role' => $partner['partner_role'] ?? 'secondaire',
                            'partner_contribution' => $partner['partner_contribution'] ?? 0,
                        ];
                    }
                }

                 $project->partners()->sync($syncData);
            }

            if ($bankAccount) {
                $project->projectBankAccount()->associate($bankAccount);
            } elseif ($bankAccount === null && array_key_exists('project_bank_account_id', $data)) {
                  $project->projectBankAccount()->dissociate();
            }

            if ($responsibleUser) {
                $project->responsible()->associate($responsibleUser);
            }

            $project->save();

            return $project;
        } catch (Exception $e) {
            throw new Exception('Error updating project: ' . $e->getMessage());
        }
    }

    public function delete(int $id): int
    {
        $project = $this->find($id);
        return $project->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return Project::whereIn('id', $ids)->delete();
    }

    public function restoreProject(int $id): Project
    {
        $project = $this->find($id);

        $exists = Project::where('project_name', $project->project_name)
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            abort(409, 'Le nom du projet est déjà utilisé par un projet actif.');
        }

        $project->restore();

        return $project;
    }

    public function findWithCollaborators(int $id): Project
    {
        return Project::with('collaborators')->find($id);
    }

    public function assignCollaborators(Project $project, array $collaboratorIds): void
    {
        $project->collaborators()->syncWithoutDetaching($collaboratorIds);
    }

    public function detachCollaborator(int $projectId, int $collaboratorId): void
    {
        $project = Project::findOrFail($projectId);
        $project->collaborators()->detach($collaboratorId);
    }

    public function findWithBudgetCategories(int $projectId): Project
    {
        return Project::with('budgetCategories')->findOrFail($projectId);
    }

    public function getBudgetLinesByCategory(int $projectId, int $categoryId): array
    {
        $project = Project::findOrFail($projectId);
        $category = BudgetCategory::findOrFail($categoryId);

        $budgetLines = $category->budgetLines()
            ->whereHas('projects', function ($q) use ($projectId) {
                $q->whereKey($projectId);
            })
            ->with([
                'projects' => function ($q) use ($projectId) {
                    $q->whereKey($projectId)->select('projects.id');
                },
            ])
            ->get();

        return [
            'project' => $project,
            'category' => $category,
            'budgetLines' => $budgetLines,
        ];
    }

    public function getProjectBudgetLines(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
         $project = Project::with(['budgetLines.partners'])->findOrFail($projectId);
        return $project->budgetLines;
    }

    public function findWithBudgetCategoriesAndAggregates(int $projectId): Project
    {
        $project = Project::findOrFail($projectId);

         $categories = BudgetCategory::query()
             ->whereHas('budgetLines.projects', function ($q) use ($projectId) {
                $q->whereKey($projectId);
            })
              ->with([
                'budgetLines' => function ($q) use ($projectId) {
                    $q->whereHas('projects', fn($p) => $p->whereKey($projectId))
                      ->with(['projects' => fn($p) => $p->whereKey($projectId)->select('projects.id')]);
                },
            ])
            ->get();

         $project->setRelation('budgetCategories', $categories);

        return $project;
    }
}
