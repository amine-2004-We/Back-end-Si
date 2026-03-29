<?php

namespace App\Repositories;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class QuoteRepository
{
    /**
     * Filtrer et paginer les devis
     */
    public function withFilters(array $filters = []): LengthAwarePaginator
    {
        $query = Quote::query()
            ->with(['supplier', 'purchaseList', 'items.article','createdBy','createdBy.collaborator.superior.user'])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['purchase_request_id'])) {
            $query->where('purchase_request_id', (int) $filters['purchase_request_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', (int) $filters['supplier_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('quote_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('quote_date', '<=', $filters['to_date']);
        }

        // recherche libre (quote_number + subject)
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('quote_number', 'ilike', "%{$s}%")
                  ->orWhere('subject', 'ilike', "%{$s}%");
            });
        }

        // filtre direct par quote_number (utile quand on ne veut pas de recherche floue)
        if (!empty($filters['quote_number'])) {
            $query->where('quote_number', 'ilike', '%' . trim($filters['quote_number']) . '%');
        }

        // actifs / supprimés
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

        $perPage = isset($filters['per_page']) ? max(1, (int) $filters['per_page']) : 10;

        return $query->paginate($perPage);
    }

    /**
     * Tous les devis (sans pagination)
     */
    public function all(): Collection
    {
        return Quote::with(['supplier', 'purchaseList','items','createdBy'])->get();
    }

    public function allWithoutPagination(): Collection
    {
        return $this->all();
    }

    /**
     * Trouver un devis (404 si non trouvé)
     */
    public function find(int $id): Quote
    {
        return Quote::findOrFail($id);
    }

    /**
     * Trouver un devis (incluant supprimés) (404 si non trouvé)
     */
    public function findWithTrashed(int $id): Quote
    {
        return Quote::withTrashed()->findOrFail($id);
    }

    /**
     * Créer un devis
     */
    public function create(array $data): Quote
    {
        // Utilise fillable du modèle
        return Quote::create($data);
    }

    /**
     * Mettre à jour un devis par ID (PO-style)
     */
    public function update(array $data, int $id): Quote
    {
        $quote = $this->find($id);
        $quote->update($data);
        return $quote;
    }

    /**
     * Supprimer (soft delete) par ID
     */
    public function delete(int $id): int
    {
        return $this->find($id)->delete();
    }

    /**
     * Supprimer plusieurs devis
     */
    public function bulkDelete(array $ids): int
    {
        return Quote::whereIn('id', $ids)->delete();
    }

    /**
     * Restaurer un devis supprimé
     */
    public function restore(int $id): Quote
    {
        $quote = $this->findWithTrashed($id);
        $quote->restore();

        return $quote;
    }

    /**
     * Synchroniser les items d'un devis
     */
     public function syncItems(Quote $quote, array $items): Quote
    {
        DB::transaction(function () use ($quote, $items) {
            // Supprimer les items existants
            $quote->items()->delete();

            $insert = [];
            $now = now();

            foreach ($items as $it) {
                if (empty($it['article_id'])) {
                    continue;
                }

                $quantity  = max(0, (float) ($it['quantity'] ?? 1));
                $unitPrice = max(0, (float) ($it['unit_price_ht'] ?? 0));
                $tvaRate   = isset($it['tva_rate']) ? max(0, min(1, (float) $it['tva_rate'])) : 0;

                $insert[] = [
                    'quote_id'      => $quote->id,
                    'article_id'    => (int) $it['article_id'],
                    'quantity'      => $quantity,
                    'unit_price_ht' => $unitPrice,
                    'tva_rate'      => $tvaRate,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            if (!empty($insert)) {
                $quote->items()->insert($insert);
            }
        });

        return $quote->load('items');
    }
}
