<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerNote;
use App\Models\StatusPartner;
use App\Repositories\PartnerRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PartnerService
{
    protected PartnerRepository $partnerRepository;

    public function __construct(PartnerRepository $partnerRepository)
    {
        $this->partnerRepository = $partnerRepository;
    }

    /**
     * Get a paginated and filtered list of partners.
     */
    public function getAllPartners(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->partnerRepository->getAllPartners($filters);
    }

    public function getAllWIthoutPagination(): Collection
    {
        return $this->partnerRepository->allWithoutPagination();
    }

    /**
     * Create a partner (Prospect).
     */
    public function createPartner(array $data): Partner
    {
         $prospectStatus = StatusPartner::where('name', 'Prospect')->first();
        if (!$prospectStatus) {
            throw new \Exception("Le statut 'Prospect' n'est pas défini dans la base de données.");
        }

        return DB::transaction(function () use ($data, $prospectStatus): Partner {
            $partnerData = collect($data)->except(['contact_people', 'notes'])->toArray();

            $partnerData['created_by_id'] = Auth::id();
            if (is_null($partnerData['created_by_id'])) {
                throw new \Exception('User ID (created_by_id) is missing.');
            }

            $partnerData['status_id'] = $prospectStatus->id;

            $partner = $this->partnerRepository->create($partnerData);

             if (!empty($data['contact_people'])) {
                foreach ($data['contact_people'] as $contact) {
                    $partner->contactPeople()->create($contact);
                }
            }

              if (!empty($data['notes'])) {
                 foreach ($data['notes'] as $noteData) {
                      if (isset($noteData['note'])) {
                         $partner->notes()->create(['note' => $noteData['note']]);
                    }
                }
            }

            return $partner->load('status');
        });
    }

   /**
     * Update an existing partner.
     *
     * @param Partner $partner
     * @param array $data
     * @return Partner
     */
    public function updatePartner(Partner $partner, array $data): Partner
    {
        DB::transaction(function () use ($partner, $data) {
             $partnerData = collect($data)->except(['contact_people', 'notes'])->toArray();
            $this->partnerRepository->update($partner->id, $partnerData);

             if (isset($data['contact_people'])) {
                $contactIdsToKeep = [];
                foreach ($data['contact_people'] as $contactData) {
                    $contactId = $contactData['id'] ?? null;
                    $contactValues = collect($contactData)->except('id')->toArray();
                    $contact = $partner->contactPeople()->updateOrCreate(
                        ['id' => $contactId],
                        $contactValues
                    );
                    $contactIdsToKeep[] = $contact->id;
                }
                 $partner->contactPeople()->whereNotIn('id', $contactIdsToKeep)->delete();
            }

             if (isset($data['notes'])) {
                $noteIdsToKeep = [];
                foreach ($data['notes'] as $noteData) {
                     if (isset($noteData['note']) && trim($noteData['note']) !== '') {
                        $noteId = $noteData['id'] ?? null;
                        $noteValues = ['note' => $noteData['note']];

                         $note = $partner->notes()->updateOrCreate(
                            ['id' => $noteId],
                            $noteValues
                        );

                        $noteIdsToKeep[] = $note->id;
                    }
                }
                // Supprimer les anciennes notes qui n'ont pas été renvoyées
                $partner->notes()->whereNotIn('id', $noteIdsToKeep)->delete();
            }

        });

        return $partner->fresh(['naturePartner', 'structurePartner', 'status', 'contactPeople', 'notes']);
    }

    /**
     * MODIFIÉ : Désactiver un ou plusieurs partenaires (Soft Delete).
     *
     * @param array $ids
     * @return array
     */
    public function desactiverPartners(array $ids): array
    {
        // Appelle le repository (sans raison)
        return $this->partnerRepository->softDeleteByIds($ids);
    }

    /**
     * AJOUTÉ : Réactiver un ou plusieurs partenaires (Restore).
     *
     * @param array $ids
     * @return array
     */
    public function reactiverPartners(array $ids): array
    {
        return $this->partnerRepository->restoreByIds($ids);
    }

    /**
     * Valide un Prospect en utilisant les données fournies
     */
    public function validerProspect(Partner $partner, array $data): Partner
    {
         $prospectStatus = StatusPartner::where('name', 'Prospect')->firstOrFail();
        $enCoursStatus = StatusPartner::where('name', 'En cours')->firstOrFail();

         if ($partner->status_id !== $prospectStatus->id) {
            throw new \Exception("Le partenaire n'est pas un 'Prospect' (statut actuel: {$partner->status->name}) et ne peut être validé.");
        }

         return DB::transaction(function () use ($partner, $data, $enCoursStatus) {

             $validationData = collect($data)->except(['contact_people'])->toArray();

             $validationData['status_id'] = $enCoursStatus->id;

             $this->partnerRepository->update($partner->id, $validationData);

             if (isset($data['contact_people'])) {
                $contactIdsToKeep = [];
                 foreach ($data['contact_people'] as $contactData) {
                     $contactId = $contactData['id'] ?? null;
                     $contactValues = collect($contactData)->except('id')->toArray();
                     if (!$contactId) {
                         $email = $contactValues['email'] ?? null;

                         if (!$email) {
                             throw new \Exception("L'email est obligatoire pour créer un contact.");
                         }

                         $emailExists = $partner->contactPeople()
                             ->where('email', $email)
                             ->exists();

                         if ($emailExists) {
                             throw new \Exception("Un contact avec cet email existe déjà : {$email}");
                         }

                         $contact = $partner->contactPeople()->create($contactValues);
                     }

                     else {
                         $contact = $partner->contactPeople()->updateOrCreate(
                             ['id' => $contactId],
                             $contactValues
                         );
                     }

                     $contactIdsToKeep[] = $contact->id;
                 }

                 $partner->contactPeople()->whereNotIn('id', $contactIdsToKeep)->delete();
            }

            return $partner->fresh(['naturePartner', 'structurePartner', 'status', 'contactPeople', 'notes']);
        });
    }

    /**
     * Ajouter une note à un partenaire.
     */
    public function addNoteToPartner(Partner $partner, string $noteContent): PartnerNote
    {
        return $partner->notes()->create([
            'note' => $noteContent,
        ]);
    }

    /**
     * AJOUTÉ : Supprime une note spécifique.
     */
    public function deleteNote(PartnerNote $note): bool
    {
        return $note->delete();
    }

    /**
     * Activer un partenaire (En cours → Partenaire actif)
     * Optionnellement assigner une phase
     */
    public function activatePartner(Partner $partner, ?int $phaseId = null): Partner
    {
        $activeStatus = StatusPartner::where('name', 'Partenaire actif')->firstOrFail();
        
        if ($partner->status->name !== 'En cours') {
            throw new \Exception("Le partenaire doit être en statut 'En cours' pour être activé (statut actuel: {$partner->status->name})");
        }

        $data = ['status_id' => $activeStatus->id, 'date_debut_partenariat' => Carbon::now()];
        if ($phaseId) {
            $data['phase_id'] = $phaseId;
        }

        $this->partnerRepository->update($partner->id, $data);
        return $partner->fresh(['status', 'phase', 'contactPeople', 'notes']);
    }

    /**
     * Clôturer un partenaire (Partenaire actif ou En cours → Clôturé)
     */
    public function closePartner(Partner $partner, string $closureReason): Partner
    {
        $closedStatus = StatusPartner::where('name', 'Clôturé')->firstOrFail();
        
        $allowedStatuses = ['En cours', 'Partenaire actif', 'Prospect'];
        if (!in_array($partner->status->name, $allowedStatuses)) {
            throw new \Exception("Impossible de clôturer un partenaire au statut '{$partner->status->name}'");
        }

        $this->partnerRepository->update($partner->id, [
            'status_id' => $closedStatus->id,
            'closure_reason' => $closureReason,
        ]);
        
        return $partner->fresh(['status', 'phase', 'contactPeople', 'notes']);
    }}