<?php

namespace App\Services;

use App\Models\Calltender;
use App\Repositories\CalltenderRepository;
use Illuminate\Support\Facades\Auth;
use App\Traits\UploadFileTrait;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CalltenderService
{
    use UploadFileTrait;

    /**
     * @var CalltenderRepository
     */
    protected CalltenderRepository $calltenderRepository;

    /**
     * @param CalltenderRepository $calltenderRepository
     */
    public function __construct(CalltenderRepository $calltenderRepository)
    {
        $this->calltenderRepository = $calltenderRepository;
    }

    /**
     * Create a new call tender.
     *
     * @param array $data Validated data from the request.
     * @return Calltender
     * @throws \RuntimeException 
     * @throws Exception
     */
    public function create(array $data): Calltender
    {

        Log::info($data);

        $data['responsible_id'] = Auth::id();
        if (isset($data['conditions_path']) && $data['conditions_path'] instanceof UploadedFile) {
            $filePath = $this->uploadPublicFile($data['conditions_path'], 'calltenders_conditions');
            $data['conditions_path'] = $filePath;
        } else {
            $data['conditions_path'] = null; 
        }

        // Diminuer le montant du marché par la somme des bons de commande validés référencés
        if (!empty($data['purchase_order_refs']) && !empty($data['total_amount'])) {
            $refs = array_map('trim', explode(',', $data['purchase_order_refs']));
            if (!empty($refs)) {
                $used = \App\Models\PurchaseOrder::whereIn('po_number', $refs)
                    ->where('status', 'approved')
                    ->sum('total_amount_ttc');
                $data['total_amount'] = (float)$data['total_amount'] - (float)$used;
            }
        }

        return $this->calltenderRepository->create($data);
    }

    /**
     * Get all call tenders.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getFilteredCalltenders(array $params)
    {
        return $this->calltenderRepository->getFilteredCalltenders($params);
    }

    /**
     * Show a specific call tender by ID.
     *
     * @param string $id
     * @return Calltender
     * @throws ModelNotFoundException
     */
    public function show(string $id): Calltender
    {
        return $this->calltenderRepository->find($id);
    }

    /**
     * Update a call tender.
     *
     * @param string $id
     * @param array $data Validated data from the request.
     * @return Calltender
     * @throws ModelNotFoundException
     * @throws \RuntimeException 
     */
    public function update(string $id, array $data): Calltender
    {
        $calltender = $this->calltenderRepository->find($id); 
        if (isset($data['conditions_path']) && $data['conditions_path'] instanceof UploadedFile) {
            if ($calltender->conditions_path) {
                $this->deletePublicFile($calltender->conditions_path);
            }
            $filePath = $this->uploadPublicFile($data['conditions_path'], 'calltenders_conditions');
            $data['conditions_path'] = $filePath;
        } elseif (array_key_exists('conditions_path', $data) && is_null($data['conditions_path'])) {
            if ($calltender->conditions_path) {
                $this->deletePublicFile($calltender->conditions_path);
            }
        } else {
            unset($data['conditions_path']);
        }
        // Diminuer le montant du marché uniquement par la somme des nouveaux bons de commande validés référencés
        if (!empty($data['purchase_order_refs']) && !empty($data['total_amount'])) {
            $newRefs = array_map('trim', explode(',', $data['purchase_order_refs']));
            $oldRefs = [];
            if (!empty($calltender->purchase_order_refs)) {
                $oldRefs = array_map('trim', explode(',', $calltender->purchase_order_refs));
            }
            // Trouver les nouveaux bons de commande ajoutés
            $addedRefs = array_diff($newRefs, $oldRefs);
            if (!empty($addedRefs)) {
                $used = \App\Models\PurchaseOrder::whereIn('po_number', $addedRefs)
                    ->where('status', 'approved')
                    ->sum('total_amount_ttc');
                $data['total_amount'] = (float)$data['total_amount'] - (float)$used;
            }
        }
        return $this->calltenderRepository->update($id, $data);
    }

    /**
     * Soft delete a call tender.
     *
     * @param string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        
        return $this->calltenderRepository->delete($id);
    }

    /**
     * Restore a soft-deleted call tender.
     *
     * @param string $id
     * @return Calltender
     * @throws Exception
     */
    public function restore(string $id): Calltender
    {
        try {
            return $this->calltenderRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

    /**
     * Bulk soft delete call tenders.
     * **IMPORTANT:** Files are NOT deleted when soft-deleting.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        // NO FILE DELETION HERE, as it's a bulk soft delete
        return $this->calltenderRepository->bulkDelete($ids);
    }

    /**
     * For updating just the file without other fields.
     *
     * @param int $id
     * @param UploadedFile $file
     * @return Calltender
     * @throws ModelNotFoundException
     * @throws \RuntimeException If file upload/deletion fails
     */
    public function updateConditionsFile(int $id, UploadedFile $file): Calltender
    {
        $calltender = $this->calltenderRepository->find($id);
        if ($calltender->conditions_path) {
            $this->deletePublicFile($calltender->conditions_path);
        }
        $filePath = $this->uploadPublicFile($file, 'calltenders_conditions');
        $calltender->conditions_path = $filePath;
        $calltender->save();
        return $calltender;
    }
   
}

