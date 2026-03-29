<?php

namespace App\Services;
use App\Repositories\GrantRepository;
use App\Models\Grant;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GrantService
{
    protected $grantRepository;

    public function __construct(GrantRepository $grantRepository)
    {
        $this->grantRepository = $grantRepository;
    }

    public function getGrants(array $params)
    {
        return $this->grantRepository->getGrants($params);
    }
    
    public function show(int $id)
    {
        return $this->grantRepository->show($id);
    }

    public function create(array $data)
    {
        if (isset($data['proof_document_attachment_path']) && $data['proof_document_attachment_path']) {
            $file = $data['proof_document_attachment_path'];
            $originalName = $file->getClientOriginalName();
            $subfolder = 'proof_documents';

            $uniqueFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $data['proof_document_attachment_path'] = $file->storeAs('grants/' . $subfolder, $uniqueFilename, 'public');
        }

        if (isset($data['payment_schedule_attachment_path']) && $data['payment_schedule_attachment_path']) {
            $file = $data['payment_schedule_attachment_path'];
            $originalName = $file->getClientOriginalName();
            $subfolder = 'payment_schedules';
            $uniqueFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $data['payment_schedule_attachment_path'] = $file->storeAs('grants/' . $subfolder, $uniqueFilename, 'public');
        }
        $data['created_by'] = auth()->id();
        $grant = $this->grantRepository->create($data);
        if ($grant) {
            return $grant;
        }
    }

   
    public function update(int $id, array $data)
    {
        $grant = $this->grantRepository->show($id);
        if (isset($data['proof_document_attachment_path']) && $data['proof_document_attachment_path']) {
            if ($grant->proof_document_attachment_path) {
                Storage::disk('public')->delete($grant->proof_document_attachment_path);
            }
            $file = $data['proof_document_attachment_path'];
            $originalName = $file->getClientOriginalName();
            $subfolder = 'proof_documents';
            $uniqueFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $data['proof_document_attachment_path'] = $file->storeAs('grants/' . $subfolder, $uniqueFilename, 'public');
        } elseif (isset($data['remove_proof_document']) && in_array($data['remove_proof_document'], ['true', '1', true, 1], true)) {
            
            if ($grant->proof_document_attachment_path) {
                $deleted = Storage::disk('public')->delete($grant->proof_document_attachment_path);
               
            }
            $data['proof_document_attachment_path'] = null;
        }
        if (isset($data['payment_schedule_attachment_path']) && $data['payment_schedule_attachment_path']) {
            if ($grant->payment_schedule_attachment_path) {
                Storage::disk('public')->delete($grant->payment_schedule_attachment_path);
            }
            $file = $data['payment_schedule_attachment_path'];
            $originalName = $file->getClientOriginalName();
            $subfolder = 'payment_schedules';
            $uniqueFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $data['payment_schedule_attachment_path'] = $file->storeAs('grants/' . $subfolder, $uniqueFilename, 'public');
        } elseif (isset($data['remove_payment_schedule']) && in_array($data['remove_payment_schedule'], ['true', '1', true, 1], true)) {
           

            if ($grant->payment_schedule_attachment_path) {
                if (Storage::disk('public')->exists($grant->payment_schedule_attachment_path)) {
                    $deleted = Storage::disk('public')->delete($grant->payment_schedule_attachment_path);
                } else {
                    FacadesLog::warning('Payment schedule file does not exist in storage:', ['path' => $grant->payment_schedule_attachment_path]);
                }
            } else {
                FacadesLog::info('No payment schedule file path to delete');
            }
            $data['payment_schedule_attachment_path'] = null;
        }
        unset($data['remove_proof_document'], $data['remove_payment_schedule']);

        $updated = $this->grantRepository->update($id, $data);
        
        return $updated;
    }

    public function delete(int $id)
    {
        return $this->grantRepository->delete($id);
    }
    
    public function restore(int $id)
    {
        return $this->grantRepository->restore($id);
    }
    
    public function bulkDelete(array $ids)
    {
        return $this->grantRepository->bulkDelete($ids);
    }
}