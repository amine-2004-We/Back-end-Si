<?php

namespace App\Repositories;

use App\Models\Convention;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\CurrencyEnum;
use App\Models\FinancialInstallment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConventionRepository
{
    /**
     * Get paginated list of Conventions with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        // ✅ UPDATED: 'projects' changed to 'project'
        $query = Convention::with(['partner', 'project', 'creator', 'installments','trackedIndividual','responsible'])
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        isset($filters['is_active']) && $filters['is_active'] === 'false'
            ? $query->onlyTrashed()
            : $query->withoutTrashed();

        if (!empty($filters['agreement_code'])) {
            $query->where('agreement_code', 'ilike', '%' . $filters['agreement_code'] . '%');
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'ilike', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['partner_id'])) {
            $query->where('partner_id', (int) $filters['partner_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['devise'])) {
            $query->where('devise', $filters['devise']);
        }


        if (!empty($filters['reporting_periodicity'])) {
            $query->where('reporting_periodicity', $filters['reporting_periodicity']);
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }


    /**
     * Get all conventions with selected columns.
     *
     * @return Collection
     */
    public function allConventions(): Collection
    {
        return Convention::all([
            'id',
            'agreement_code',
            'type',
            'partner_id',
            'project_id', // ✅ ADDED
            'signed_at',
            'estimated_end_date',
            'amount',
            'reporting_periodicity',
            'status',
            'signed_document',
            'title',
            'created_by',
            'devise',
        ]);
    }

    /**
     * Find a Convention by ID, including soft deleted.
     *
     * @param int $id
     * @return Convention
     * @throws ModelNotFoundException
     */
    public function find(int $id): Convention
    {
        // ✅ UPDATED: 'projects' changed to 'project'
        return Convention::with(['partner', 'project','installments','responsible'])->findOrFail($id);
    }

    /**
     * Create a new Convention.
     *
     * @param array $data
     * @return Convention
     */
    public function create(array $data): Convention
    {
        try {
            $convention = DB::transaction(function () use ($data) {

                $installmentsData = $data['installments'] ?? [];
                unset($data['installments']);

                if (isset($data['devise'])) {
                    $data['devise'] = CurrencyEnum::from($data['devise'])->value;
                }

                if (isset($data['signed_document']) && $data['signed_document'] instanceof \Illuminate\Http\UploadedFile) {
                    $data['signed_document'] = $data['signed_document']->store('conventions', 'private');
                }

                $convention = Convention::create($data);

                if (!empty($installmentsData)) {
                    $convention->installments()->createMany($installmentsData);
                }

                return $convention;
            });

            return $convention;
        } catch (\Throwable $e) {
            Log::error("Erreur Critique lors de la création transactionnelle de Convention: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a Convention by ID.
     *
     * @param int $id
     * @param array $data
     * @return Convention
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Convention
    {
        try {
            $convention = DB::transaction(function () use ($id, $data) {

                $convention = $this->find($id);

                if (isset($data['signed_document']) && $data['signed_document'] instanceof \Illuminate\Http\UploadedFile) {
                    $data['signed_document'] = $data['signed_document']->store('conventions', 'private');
                }

                if (isset($data['devise'])) {
                    $data['devise'] = \App\Enums\CurrencyEnum::from($data['devise'])->value;
                }

                $installmentsData = null;
                $processInstallments = array_key_exists('installments', $data);

                if ($processInstallments) {
                    $installmentsData = $data['installments'];
                    unset($data['installments']);
                }

                $convention->update($data);

                if ($processInstallments) {

                    if (is_array($installmentsData) && !empty($installmentsData)) {
                        $currentInstallmentIds = $convention->installments()->pluck('id')->toArray();
                        $incomingInstallmentIds = [];

                        foreach ($installmentsData as $installment) {

                            if (isset($installment['id'])) {
                                $installmentId = $installment['id'];
                                $incomingInstallmentIds[] = $installmentId;

                                $existingInstallment = FinancialInstallment::find($installmentId);
                                if ($existingInstallment) {
                                    $existingInstallment->update($installment);
                                }
                            } else {
                                $newInstallment = $convention->installments()->create($installment);
                                $incomingInstallmentIds[] = $newInstallment->id;
                            }
                        }

                        $installmentsToDelete = array_diff($currentInstallmentIds, $incomingInstallmentIds);
                        if (!empty($installmentsToDelete)) {
                            FinancialInstallment::whereIn('id', $installmentsToDelete)->delete();
                        }
                    } else {
                        $convention->installments()->delete();
                    }
                }

                $convention->refresh();
                return $convention;
            });

             return $convention->fresh(['partner', 'project', 'installments']);
        } catch (\Throwable $e) {
            Log::error("Erreur Critique lors de la mise à jour transactionnelle de Convention (ID: $id): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a Convention by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $convention = $this->find($id);
        return $convention->delete();
    }

    /**
     * Bulk delete Conventions by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Convention::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Convention by ID.
     *
     * @param int $id
     * @return Convention
     */
    public function restore(int $id): Convention
    {
        $convention = Convention::onlyTrashed()->findOrFail($id);
        $convention->installments()->onlyTrashed()->restore();
        $convention->restore();
        return $convention;
    }

    /**
     * Récupère le chemin complet du document signé d'une convention.
     **/
    public function getSignedDocumentPath(Convention $convention): ?string
    {
        return $convention->signed_document ? storage_path('app/private/' . $convention->signed_document) : null;
    }

}