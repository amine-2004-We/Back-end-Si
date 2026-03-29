<?php

namespace App\Services;

use App\Models\Collaborator;
use App\Models\ExternalTrainer;
use App\Models\Module;
use App\Models\Site;
use App\Models\Training;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\TrainingSessionAttachment;
use App\Repositories\TrainingSessionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 *class TrainingSessionService
 */
class TrainingSessionService
{
    /**
     * @param TrainingSessionRepository $repository
     */
    public function __construct(public TrainingSessionRepository $repository) {}

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * @param int $id
     * @return TrainingSession|null
     */
    public function find(int $id): ?TrainingSession
    {
        return $this->repository->find($id);
    }

    /**
     * @param array $data
     * @return TrainingSession
     */
    public function create(array $data): TrainingSession
    {
        return DB::transaction(function () use ($data) {
            $attachments = Arr::pull($data, 'attachments', []);
            $data['created_by_id'] = auth()->id();

            $session = $this->repository->create($data);
            $this->handleAttachments($session, $attachments);

            return $session->load($this->repository->defaultWith);
        });
    }

    /**
     * @param TrainingSession $session
     * @param array $data
     * @return TrainingSession
     */
    public function update(TrainingSession $session, array $data): TrainingSession
    {
        return DB::transaction(function () use ($session, $data) {
            $attachments = Arr::pull($data, 'attachments', []);
            $attachmentsToDelete = Arr::pull($data, 'attachments_to_delete', []);

            $this->repository->update($session, $data);

            if (!empty($attachmentsToDelete)) {
                $this->deleteAttachments($attachmentsToDelete);
            }
            if (!empty($attachments)) {
                $this->handleAttachments($session, $attachments);
            }

            return $session->fresh($this->repository->defaultWith);
        });
    }

    /**
     * Toggle activation status for multiple sessions.
     *
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        return $this->repository->toggleActivation($ids);
    }

    /**
     * Get options for form dropdowns.
     *
     * @return array
     */
    public function getFormOptions(): array
    {
        $collaborators = Collaborator::select('id', 'first_name', 'last_name')->get()->map(fn($c) => [
            'value' => $c->id,
            'label' => "{$c->first_name} {$c->last_name} (Interne)",
            'type' => Collaborator::class
        ]);

        $externalTrainers = ExternalTrainer::select('id', 'full_name')->get()->map(fn($e) => [
            'value' => $e->id,
            'label' => "{$e->full_name} (Externe)",
            'type' => ExternalTrainer::class
        ]);

        return [
            'modules' => Module::select('id', 'title')->get(),
            'trainings' => Training::select('id', 'title')->get(),
            'trainingGroups' => TrainingGroup::select('id', 'title')->get(),
            'animators' => $collaborators->concat($externalTrainers),
            'sites' => Site::select('id', 'name')->get(),
            'sessionTypes' => ['Theorique', 'Pratique', 'Evaluation'],
            'statuses' => ['Planifiée', 'Réalisée', 'Reportée', 'Annulée'],
        ];
    }

    /**
     * Store uploaded attachments for a training session.
     *
     * @param TrainingSession $session
     * @param array $files
     * @return void
     */
    private function handleAttachments(TrainingSession $session, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('training_sessions', 'public');
            $session->attachments()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Deletes attachments from storage and the database.
     *
     * @param array $attachmentIds
     * @return void
     */
    private function deleteAttachments(array $attachmentIds): void
    {
        $attachments = TrainingSessionAttachment::whereIn('id', $attachmentIds)->get();
        foreach($attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
    }
}
