<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use App\Traits\UploadFileTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    use UploadFileTrait;

    protected SupplierRepository $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * Create a new supplier and handle the upload of associated documents and contacts.
     */
    public function create(array $data): Supplier
    {
        return DB::transaction(function () use ($data): Supplier {
            $supplierData = collect($data)->except(['contact_people', 'supporting_documents'])->toArray();
            
            if (!empty($data['supporting_documents']) && is_array($data['supporting_documents'])) {
                $uploadedPaths = [];
                foreach ($data['supporting_documents'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $uploadedPaths[] = $this->uploadPublicFile($file, 'suppliers_documents');
                    }
                }
                $supplierData['supporting_documents'] = json_encode($uploadedPaths);
            }

            $supplier = $this->supplierRepository->create($supplierData);

            $contactPeopleData = [];
            if (isset($data['contact_people'])) {
                $contactPeopleData = $data['contact_people'] ?? [];
            }
    
            if (is_array($contactPeopleData)) {
                foreach ($contactPeopleData as $contact) {

                    $cleanedContact = $this->cleanContactData($contact);
                    
                    if ($this->hasValidContactData($cleanedContact)) {
                        $supplier->contactPeople()->create($cleanedContact);
                    }
                }
            }
    
            return $supplier;
        });
    }

    public function getFilteredSuppliers(array $params)
    {
        return $this->supplierRepository->getFilteredSuppliers($params);
    }

    public function show(string $id): Supplier
    {
        return $this->supplierRepository->find($id);
    }

    /**
     * Update supplier details, documents, and contacts.
     */
    public function update(int $id, array $data): Supplier
    {
        return DB::transaction(function () use ($id, $data): Supplier {
            $supplier = $this->supplierRepository->find($id);
    
            $contactPeopleData = [];
            if (isset($data['contact_people'])) {
                $contactPeopleData = $data['contact_people'] ?? [];
            }
            $supplierData = collect($data)->except(['contact_people'])->toArray();
    
            $supplierData = $this->handleDocumentUpdates($supplier, $supplierData);
            
            $supplier->update($supplierData);
    
            $supplier->contactPeople()->delete();
    
            if (is_array($contactPeopleData)) {
                foreach ($contactPeopleData as $contact) {

                    $cleanedContact = $this->cleanContactData($contact);
                    
                    if ($this->hasValidContactData($cleanedContact)) {
                        $supplier->contactPeople()->create($cleanedContact);
                    }
                }
            }
    
            return $supplier->fresh();
        });
    }

    /**
     * Helper method to handle adding/removing files during an update.
     * @param Supplier $supplier
     * @param array $data
     * @return array
     */
    protected function handleDocumentUpdates(Supplier $supplier, array $data): array
    {
        $existingPaths = json_decode($supplier->supporting_documents, true) ?: [];
    
        // 2. Handle documents to be removed
        if (!empty($data['removed_documents']) && is_string($data['removed_documents'])) {
            $removedPaths = json_decode($data['removed_documents'], true) ?: [];
            
            foreach ($removedPaths as $pathToRemove) {
                // Delete the file from storage
                Storage::disk('public')->delete($pathToRemove);
            }
            
            $existingPaths = array_diff($existingPaths, $removedPaths);

            unset($data['removed_documents']);
        }
        
        $newPaths = [];
        if (!empty($data['supporting_documents']) && is_array($data['supporting_documents'])) {
            foreach ($data['supporting_documents'] as $file) {
                if ($file instanceof UploadedFile) {
                    $newPaths[] = $this->uploadPublicFile($file, 'suppliers_documents');
                }
            }
    
            unset($data['supporting_documents']);
        }
    
        $allDocumentPaths = array_merge($existingPaths, $newPaths);
        $data['supporting_documents'] = json_encode(array_values($allDocumentPaths));
    
        return $data;
    }


    /**
     * Append a single file to the supplier's supporting documents.
     */
    public function updateDocumentFile(Supplier $supplier, UploadedFile $file): Supplier
    {
        $uploadedPaths = json_decode($supplier->supporting_documents, true) ?: [];
        $uploadedPaths[] = $this->uploadPublicFile($file, 'suppliers_documents');

        $supplier->update([
            'supporting_documents' => json_encode($uploadedPaths),
        ]);

        return $supplier;
    }

    protected function uploadPublicFile(UploadedFile $file, string $folder): string
    {
        return Storage::disk('public')->putFile($folder, $file);
    }

    public function delete(int $id): ?bool
    {
        return $this->supplierRepository->delete($id);
    }

    public function restore(string $id): Supplier
    {
        return $this->supplierRepository->restore($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->supplierRepository->bulkDelete($ids);
    }

    /**
     * Download a file from public disk.
     */
    public function downloadDocument(string $fileName): StreamedResponse
    {
        $filePath = 'suppliers_documents/' . $fileName;

        if (!Storage::disk('public')->exists($filePath)) {
            throw new ModelNotFoundException('File not found at path: ' . $filePath);
        }

        return Storage::disk('public')->download($filePath, $fileName);
    }

    /**
     * Convertir les chaînes vides en null
     */
    private function cleanContactData(array $contact): array
    {
        return array_map(function ($value) {
            if (is_string($value) && trim($value) === '') {
                return null;
            }
            return $value;
        }, $contact);
    }

    /**
     * Vérifier si le contact a au moins un champ rempli
     */
    private function hasValidContactData(array $contact): bool
    {
        $fieldsToCheck = ['first_name', 'last_name', 'email', 'phone', 'position', 'address'];
        
        foreach ($fieldsToCheck as $field) {
            if (!empty($contact[$field])) {
                return true;
            }
        }
        
        return false;
    }
}

