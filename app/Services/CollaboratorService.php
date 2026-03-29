<?php

namespace App\Services;

use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use App\Models\Collaborator;
use App\Models\Position;
use App\Models\User;
use App\Repositories\CollaboratorRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class CollaboratorService
{
    protected CollaboratorRepository $collaboratorRepository;

    public function __construct(CollaboratorRepository $collaboratorRepository)
    {
        $this->collaboratorRepository = $collaboratorRepository;
    }

    public function getAll()
    {
        return $this->collaboratorRepository->getAll();
    }

    public function findCollaboratorById(string $collaboratorId): Collaborator
    {
        return $this->collaboratorRepository->find($collaboratorId);
    }

    public function create(StoreCollaboratorRequest $request): Collaborator
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();

                $data['password'] = isset($data['password'])
                    ? Hash::make($data['password'])
                    : null;

                $user = User::create([
                    'name'     => $data['username'] ?? ($data['first_name'] . ' ' . $data['last_name']),
                    'email'    => strtolower($data['email']),
                    'password' => $data['password'],
                ]);

                $data['user_id'] = $user->id;

                if (!empty($data['position_id'])) {
                    $position = Position::query()->findOrFail($data['position_id']);
                    $roleName = substr($position->title, 0, 255);
                    $role = Role::firstOrCreate(['name' => $roleName]);
                    $user->assignRole($role);
                }

                if ($request->hasFile('photo')) {
                    $data['photo'] = $request->file('photo')->store('photos', 'public');
                }

                $collaborator = $this->collaboratorRepository->create($data);

                if (!empty($data['projects']) && is_array($data['projects'])) {
                    $collaborator->projects()->sync($data['projects']);
                }

                return $collaborator;
            });
        } catch (\Throwable $e) {
            if (isset($data['photo'])) {
                Storage::disk('public')->delete($data['photo']);
            }
            throw $e;
        }
    }

    public function update(string $collaboratorId, UpdateCollaboratorRequest $request)
    {
        $data = $request->validated();
        $position = Position::findOrFail($data['position_id']);
        if ((int)$data['department_id'] != (int)$position->department_id)
        {
            abort(422, 'Le poste sélectionné n’appartient pas au organigrame indiqué.');
        }
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $collaborator = $this->collaboratorRepository->update($collaboratorId, $data);

        if (isset($data['projects']) && is_array($data['projects'])) {
            $collaborator->projects()->sync($data['projects']);
        }

        return $collaborator;

    }

    public function delete(string $collaboratorId): bool
    {
        return $this->collaboratorRepository->delete($collaboratorId);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->collaboratorRepository->bulkDelete($ids);
    }

    public function getAllWithTrashed(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->collaboratorRepository->getAllTrashedCollaborators($filters);
    }

    public function getAllCollaborators(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->collaboratorRepository->getAllCollaborators($filters);
    }

    public function getTrashed(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->collaboratorRepository->getAllTrashedCollaborators($filters);
    }

    public function restoreCollaborator(int $id): Collaborator
    {
        $collaborator = $this->collaboratorRepository->findTrashedById($id);

        if (!$collaborator) {
            throw new ModelNotFoundException("Deleted collaborator not found.");
        }

        $this->collaboratorRepository->restore($collaborator);

        return $collaborator;
    }

    public function findDeletedCollaboratorById(string $id): ?Collaborator
    {
        return $this->collaboratorRepository->findTrashedById($id);
    }

    public function assignProjects(Collaborator $collaborator, array $projectIds): void
    {
        $collaborator->projects()->syncWithoutDetaching($projectIds);
    }

    public function unassignProject(Collaborator $collaborator, int $projectId): void
    {
        $collaborator->projects()->detach($projectId);
    }

    public function syncProjects(Collaborator $collaborator, array $projectIds): void
    {
        $collaborator->projects()->sync($projectIds);
    }

    public function getCollaboratorWithProjects(int $id)
    {
        return $this->collaboratorRepository->findWithProjects($id);
    }

    public function getCollaboratorWithUserId(int $id)
    {
        return $this->collaboratorRepository->findCollaboratorWithUserID($id);
    }
}
