<?php

namespace App\Services;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderTermsFileRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Repositories\PurchaseOrderRepository;
use App\Traits\UploadFileTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    use UploadFileTrait;

    /** @var PurchaseOrderRepository */
    protected PurchaseOrderRepository $purchaseOrderRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
    }

    /**
     * Liste avec filtres & pagination
     */
    public function getAll(Request $request)
    {
        $filters = $request->only([
            'id',
            'po_number',
            'status',
            'supplier_id',
            'quote_id',
            'issuer_id',
            'issue_date_from',
            'issue_date_to',
            'is_active',
            'per_page',
        ]);

        return $this->purchaseOrderRepository->withFilters($filters);
    }

    /**
     * Sans pagination
     */
    public function getAllWithoutPagination()
    {
        return $this->purchaseOrderRepository->allWithoutPagination();
    }

    /**
     * Détail
     * @throws ModelNotFoundException
     */
    public function find(int $id): PurchaseOrder
    {
        return $this->purchaseOrderRepository->find($id);
    }

    /**
     * Création du bon de commande
     * - upload du fichier terms_file si fourni (disk public, dossier purchase_orders/terms)
     * - attache des purchase_requests si fournis
     */
    public function create(array $data, StorePurchaseOrderRequest $request): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $request) {
            $filePath = $this->uploadPublicFile(
                $request->file('terms_file'),
                'purchase_orders/terms'
            );

            $items = $data['items'] ?? [];
            unset($data['items']);

            if ($filePath) {
                $data['terms_file_path'] = $filePath;
            }

            // purchase_request_id doit être présent dans $data
            $po = $this->purchaseOrderRepository->create($data);

            \Log::info($items);
            if (!empty($items)) {
                $lines = [];
                foreach ($items as $item) {
                    $lines[] = [
                        'purchase_order_id' => $po->id,
                        'article_id'        => $item['article_id'],
                        'product_id'        => $item['product_id'],
                        'quantity'          => $item['quantity'],
                        'unit_price'        => $item['unit_price_ht'],
                        'tva_rate'          => $item['tva_rate'],
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
                PurchaseOrderLine::insert($lines);
            }

            return $po;
        });
    }
    /**
     * Mise à jour standard (sans gestion du fichier ici — voir updateTermsFile)
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): PurchaseOrder
    {
        // Nouvelle logique : on attend purchase_request_id dans $data
        // (plus de gestion de collection ou de pivot)
        return $this->purchaseOrderRepository->update($data, $id);
    }

    /**
     * Mise à jour du fichier des conditions (remplacement/suppression)
     * - Si un nouveau fichier est envoyé -> remplacé et l’ancien est supprimé
     * - Si remove_terms_file=true (et pas de nouveau fichier) -> suppression de l’ancien
     * @return null
     * @throws ModelNotFoundException
     */
    public function updateTermsFile(int $id, UpdatePurchaseOrderTermsFileRequest $request): null
    {
        $po = $this->purchaseOrderRepository->find($id);

        $oldPath = $po->terms_file_path;

        if ($request->hasFile('terms_file')) {
            $newPath = $this->uploadPublicFile($request->file('terms_file'), 'purchase_orders/terms');
            $this->purchaseOrderRepository->update(['terms_file_path' => $newPath], $id);

            if ($oldPath) {
                $this->deletePublicFile($oldPath);
            }

            return null;
        }

        if ($request->boolean('remove_terms_file') === true) {
            $this->purchaseOrderRepository->update(['terms_file_path' => null], $id);

            if ($oldPath) {
                $this->deletePublicFile($oldPath);
            }
        }

        return null;
    }

    /**
     * Suppression (soft delete)
     */
   public function delete(int $id): int
   {
       if (!$id || $id <= 0) {
           throw new \InvalidArgumentException(
               "ID invalide pour la suppression. Reçu: " .
               (is_null($id) ? 'NULL' : var_export($id, true))
           );
       }

       try {
           $result = $this->purchaseOrderRepository->delete($id);

           if ($result === 0) {
               throw new \RuntimeException("Aucun bon de commande supprimé pour l'ID: {$id}");
           }

           return $result;

       } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
           throw new \RuntimeException("Bon de commande non trouvé avec l'ID: {$id}", 404, $e);
       }
   }

    /**
     * Suppression en masse
     */
    public function bulkDestroy(array $ids): int
    {
        return $this->purchaseOrderRepository->bulkDelete($ids);
    }

    /**
     * Restauration
     */
    public function restore(int $id): PurchaseOrder
    {
        try {
            return $this->purchaseOrderRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
    public function getPurchaseOrderLines(int $id):Collection
    {
        try {
            return $this->purchaseOrderRepository->getPurchaseOrderLines($id);
        }catch (\Exception $e){
            abort(404, $e->getMessage());
        }
    }
}
