<?php

namespace App\Services;

use App\Models\External;
use App\Models\Participant;
use App\Repositories\ExternalRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class ExternalService
{
    public function __construct(protected ExternalRepository $externalRepository)
    {
    }

    public function getPaginatedExternals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->externalRepository->getPaginated($filters, $perPage);
    }

    public function findExternalById(int $id): ?External
    {
        return $this->externalRepository->findById($id);
    }

     public function createExternal(array $data): External
     {
         return DB::transaction(function () use ($data) {
             $trainingData = Arr::pull($data, 'trainings', []);
             $participantData = Arr::pull($data, 'participant_details', []);
             $attachments = Arr::pull($data, 'attachments', []);

             $participant = Participant::create([
                 'insured' => true,
                 'comments' => $participantData['comments'] ?? null,
                 'type' => 'external',
                 'created_by_id' => Auth::id(),
             ]);

             $data['participant_id'] = $participant->id;
             $data['created_by'] = Auth::id();

             $external = $this->externalRepository->create($data);

             if (!empty($attachments)) {
                 foreach ($attachments as $file) {
                     $path = $file->store('externals/attachments', 'public');
                     $external->attachments()->create([
                         'file_path' => $path,
                         'original_name' => $file->getClientOriginalName(),
                         'mime_type' => $file->getClientMimeType(),
                         'size' => $file->getSize(),
                     ]);
                 }
             }

             if (!empty($trainingData)) {
                 $this->externalRepository->syncTrainings($external, $trainingData);
             }

             return $external->load(['attachments']);
         });
     }

    public function updateExternal(External $external, array $data): External
    {
        return DB::transaction(function () use ($external, $data) {
            // **FIX: Use Arr::pull for cleaner data handling**
            $trainingData = Arr::pull($data, 'trainings');
            $participantData = Arr::pull($data, 'participant_details');
            $newAttachments = Arr::pull($data, 'attachments', []);
            $attachmentsToDelete = Arr::pull($data, 'attachments_to_delete', []);

            if (!is_null($trainingData)) {
                $this->externalRepository->syncTrainings($external, $trainingData);
            }
            if (!is_null($participantData) && $external->participant) {
                $external->participant->update($participantData);
            }

            // **FIX: Call the new repository method to delete specific attachments**
            if (!empty($attachmentsToDelete)) {
                $this->externalRepository->deleteAttachments($attachmentsToDelete);
            }

            // Add new attachments
            if (!empty($newAttachments)) {
                foreach ($newAttachments as $file) {
                    $path = $file->store('externals/attachments', 'public');
                    $external->attachments()->create([
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            // Update the remaining fields on the external model
            $this->externalRepository->update($external, $data);
            
            return $external->fresh($this->externalRepository->defaultWith);
        });
    }

    public function deleteExternal(External $external): ?bool
    {
        return $this->externalRepository->delete($external);
    }

    public function toggleExternalActivation(array $ids): array
    {
        return $this->externalRepository->toggleActivation($ids);
    }
}
