<?php

namespace App\Services;

use App\Models\ExpenseNote;
use App\Models\Training;
use App\Models\BudgetLine;
use App\Models\User;
use App\Models\Collaborator;
use App\Models\Trainer;
use App\Models\Participant;
use App\Repositories\ExpenseNoteRepository;
use App\Traits\UploadFileTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo; // ✅ important
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseNoteService
{
    use UploadFileTrait;

    protected ExpenseNoteRepository $repo;

    public function __construct(ExpenseNoteRepository $repo)
    {
        $this->repo = $repo;
    }

    /** Liste paginée avec filtres. */
    public function getAll(Request $request): LengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'code',
            'validation_status',
            'expense_nature',
            'training_id',
            'budget_line_id',
            'beneficiary_type',
            'beneficiary_id',
            'created_by_id',
            'expense_from',
            'expense_to',
            'amount_min',
            'amount_max',
            'per_page',
        ]);

        $perPage = (int)($filters['per_page'] ?? 10);

        return $this->repo
            ->allWithFilters($filters)
            ->with([
                // ✅ Eager-loading conditionnel selon le type réel du morphTo "beneficiary"
                'beneficiary' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Collaborator::class => [
                            // ajouter d'autres relations si besoin (ex: 'department')
                        ],
                        Trainer::class => [
                            'internalTrainer.collaborator',
                            'externalTrainer',
                        ],
                        Participant::class => [
                            'internalParticipant.collaborator',
                            'externalParticipant', // adapte si ton nom diffère
                        ],
                    ]);
                },
                'training',
                'budgetLine',
                'createdBy',
            ])
            ->paginate($perPage);
    }

    /** Liste légère (sélecteurs). */
    public function getAllWithoutPagination(): Collection
    {
        return $this->repo->allWithoutPagination();
    }

    /** Lecture d’une note (avec relations). */
    public function get(int $id): ExpenseNote
    {
        return $this->repo->findWithRelations($id);
    }

    /**
     * Création.
     * $data attendu:
     *  - beneficiary_type: collaborator|trainer|participant
     *  - beneficiary_id: int
     *  - training_id: int
     *  - budget_line_id: int
     *  - expense_date: Y-m-d
     *  - expense_nature: transport|lodging|meals|misc
     *  - amount_ttc: numeric >= 0
     *  - comments?: string|null
     *  - (attachment file passé séparément)
     */
    public function create(array $data, int $createdById, ?UploadedFile $attachmentFile = null): ExpenseNote
    {
        return DB::transaction(function () use ($data, $createdById, $attachmentFile) {

            $beneficiary = $this->resolveBeneficiary(
                $data['beneficiary_type'] ?? null,
                $data['beneficiary_id']   ?? null
            );

            $training   = Training::findOrFail((int)$data['training_id']);
            $budgetLine = BudgetLine::findOrFail((int)$data['budget_line_id']);
            $creator    = User::findOrFail($createdById);

            $this->assertPayload($data);

            // Upload du fichier si fourni
            $attachmentPath = $this->uploadPublicFile($attachmentFile, 'expense_notes');

            // 'code' auto via Observer; statut défaut 'draft' si non fourni
            $payload = [
                'code'              => $data['code']            ?? null,
                'expense_date'      => $data['expense_date'],
                'expense_nature'    => $data['expense_nature'],
                'amount_ttc'        => $data['amount_ttc'],
                'attachment_path'   => $attachmentPath, // peut être null si pas de fichier
                'validation_status' => $data['validation_status'] ?? ExpenseNote::STATUS_DRAFT,
                'comments'          => $data['comments'] ?? null,
            ];

            return $this->repo->createWithAssociations(
                data:        $payload,
                beneficiary: $beneficiary,
                training:    $training,
                budgetLine:  $budgetLine,
                creator:     $creator
            );
        });
    }

    /**
     * Mise à jour (associations optionnelles).
     * - $newAttachment : remplace le fichier existant
     * - $removeAttachment : supprime le fichier existant (si true)
     */
    public function update(
        int $id,
        array $data,
        ?UploadedFile $newAttachment = null,
        bool $removeAttachment = false
    ): ExpenseNote {
        return DB::transaction(function () use ($id, $data, $newAttachment, $removeAttachment) {

            $note = $this->repo->find($id); // récupère l’existant (avec trashed)
            
            $beneficiary = null;
            if (!empty($data['beneficiary_type']) && !empty($data['beneficiary_id'])) {
                $beneficiary = $this->resolveBeneficiary($data['beneficiary_type'], (int)$data['beneficiary_id']);
            }

            $training = !empty($data['training_id']) ? Training::findOrFail((int)$data['training_id']) : null;
            $budget   = !empty($data['budget_line_id']) ? BudgetLine::findOrFail((int)$data['budget_line_id']) : null;
            $creator  = !empty($data['created_by_id']) ? User::findOrFail((int)$data['created_by_id']) : null;

            if (isset($data['amount_ttc']) && $data['amount_ttc'] < 0) {
                throw ValidationException::withMessages(['amount_ttc' => 'Le montant TTC doit être ≥ 0.']);
            }

            // Nettoyage des colonnes modifiables
            $payload = array_intersect_key($data, array_flip([
                'code',
                'expense_date',
                'expense_nature',
                'amount_ttc',
                'validation_status',
                'comments',
            ]));

            if (isset($payload['validation_status'])) {
                $this->assertStatusValue($payload['validation_status']);
            }
            if (isset($payload['expense_nature'])) {
                $this->assertNatureValue($payload['expense_nature']);
            }

            // Gestion des pièces jointes
            if ($removeAttachment || (!empty($data['attachment_remove']) && $data['attachment_remove'])) {
                // supprimer l’ancien fichier si présent
                if (!empty($note->attachment_path)) {
                    $this->deletePublicFile($note->attachment_path);
                }
                $payload['attachment_path'] = null;
            }

            if ($newAttachment) {
                // remplacer: delete + upload
                if (!empty($note->attachment_path)) {
                    $this->deletePublicFile($note->attachment_path);
                }
                $payload['attachment_path'] = $this->uploadPublicFile($newAttachment, 'expense_notes');
            }

            return $this->repo->update(
                id:         $note->id,
                data:       $payload,
                beneficiary:$beneficiary,
                training:   $training,
                budgetLine: $budget,
                creator:    $creator
            );
        });
    }

    /** Suppression multiple (soft delete). */
    public function deleteMany(array $ids): int
    {
        return $this->repo->bulkDelete($ids);
    }

    /** Restauration. */
    public function restore(int $id): ExpenseNote
    {
        return $this->repo->restore($id);
    }

    /** Mise à jour du statut (avec garde simple à l’approbation). */
    public function updateStatus(int $id, string $status, ?string $comments = null): ExpenseNote
    {
        $this->assertStatusValue($status);

        $note = $this->repo->find($id);

        if ($status === ExpenseNote::STATUS_APPROVED && $note->expense_date && $note->expense_date->isFuture()) {
            throw ValidationException::withMessages([
                'expense_date' => "La date de dépense ne peut pas être future lors de l'approbation.",
            ]);
        }

        return $this->repo->updateStatus($id, $status, $comments);
    }

    /** Options UI. */
    public function getOptions(): array
    {
        return [
            'beneficiary_types' => [
                'collaborator' => 'Collaborateur',
                'trainer'      => 'Formateur',
                'participant'  => 'Participant',
            ],
            'expense_natures' => [
                ExpenseNote::NATURE_TRANSPORT => 'Transport',
                ExpenseNote::NATURE_LODGING   => 'Hébergement',
                ExpenseNote::NATURE_MEALS     => 'Repas',
                ExpenseNote::NATURE_MISC      => 'Divers',
            ],
            'validation_statuses' => [
                ExpenseNote::STATUS_DRAFT     => 'Saisie',
                ExpenseNote::STATUS_IN_REVIEW => 'En validation',
                ExpenseNote::STATUS_APPROVED  => 'Validée',
                ExpenseNote::STATUS_REJECTED  => 'Rejetée',
            ],
        ];
    }

    /** Résolution du bénéficiaire polymorphe. */
    private function resolveBeneficiary(?string $type, ?int $id): ?Model
    {
        if (!$type || !$id) {
            return null;
        }

        $class = match ($type) {
            'collaborator', Collaborator::class => Collaborator::class,
            'trainer',      Trainer::class      => Trainer::class,
            'participant',  Participant::class  => Participant::class,
            default => null,
        };

        if (!$class) {
            throw ValidationException::withMessages([
                'beneficiary_type' => "Type de bénéficiaire invalide: {$type}.",
            ]);
        }

        return $class::findOrFail((int)$id);
    }

    /** Contrôles de base. */
    private function assertPayload(array $data): void
    {
        if (!isset($data['expense_nature'])) {
            throw ValidationException::withMessages(['expense_nature' => 'La nature de la dépense est requise.']);
        }
        if (!isset($data['amount_ttc']) || $data['amount_ttc'] < 0) {
            throw ValidationException::withMessages(['amount_ttc' => 'Le montant TTC doit être ≥ 0.']);
        }
        $this->assertNatureValue($data['expense_nature']);

        if (isset($data['validation_status'])) {
            $this->assertStatusValue($data['validation_status']);
        }
    }

    private function assertNatureValue(string $value): void
    {
        $allowed = [
            ExpenseNote::NATURE_TRANSPORT,
            ExpenseNote::NATURE_LODGING,
            ExpenseNote::NATURE_MEALS,
            ExpenseNote::NATURE_MISC,
        ];
        if (!in_array($value, $allowed, true)) {
            throw ValidationException::withMessages(['expense_nature' => 'Valeur de nature invalide.']);
        }
    }

    private function assertStatusValue(string $value): void
    {
        $allowed = [
            ExpenseNote::STATUS_DRAFT,
            ExpenseNote::STATUS_IN_REVIEW,
            ExpenseNote::STATUS_APPROVED,
            ExpenseNote::STATUS_REJECTED,
        ];
        if (!in_array($value, $allowed, true)) {
            throw ValidationException::withMessages(['validation_status' => 'Statut de validation invalide.']);
        }
    }
}
