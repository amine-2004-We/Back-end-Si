<?php

namespace App\Services;

use App\Http\Requests\StoreProjectBankAccountRequest;
use App\Http\Requests\UpdateBankAccountSupportingDocumentRequest;
use App\Models\ProjectBankAccount;
use App\Repositories\ProjectBankAccountRepository;
use App\Traits\UploadFileTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


/**
* class ProjectBankAccountService
 */
class ProjectBankAccountService
{
    use UploadFileTrait;

    /** @var ProjectBankAccountRepository */
    protected ProjectBankAccountRepository $bankAccountRepository;

    /**
     * @param ProjectBankAccountRepository $bankAccountRepository
     */
    public function __construct(ProjectBankAccountRepository $bankAccountRepository)
    {
        $this->bankAccountRepository = $bankAccountRepository;
    }


    public function getAll(Request $request)
    {
        $filters = $request->only(['agency','rib_iban','account_title','account_holder_name','bank','is_active','status','per_page']);
        return $this->bankAccountRepository->allWithFilters($filters);
    }


    /**
     * @param int $id
     * @return ProjectBankAccount
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProjectBankAccount
    {
        return $this->bankAccountRepository->find($id);
    }

    public function getAllWithoutPagination()
    {
        return $this->bankAccountRepository->allWithoutPagination();
    }

    /**
     * @param StoreProjectBankAccountRequest $request
     * @param array $data
     * @return ProjectBankAccount
     */
    public function create(array $data,StoreProjectBankAccountRequest $request): ProjectBankAccount
    {
        $fileName = $this->uploadPublicFile($request->file('supporting_document'),'project_bank_accounts');
        return $this->bankAccountRepository->create($data,$fileName);
    }

    /**
     * @param int $id
     * @param array $data
     * @return ProjectBankAccount
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): ProjectBankAccount
    {
        return $this->bankAccountRepository->update($id, $data);
    }

    /**
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->bankAccountRepository->delete($id);
    }

    /**
     * @param int $id
     * @param UpdateBankAccountSupportingDocumentRequest $request
     * @return null
     */
    public function updateSupportingDocument(int $id, UpdateBankAccountSupportingDocumentRequest $request): null
    {
        $file = $this->uploadPublicFile($request->file('supporting_document'),'project_bank_accounts');
        return $this->bankAccountRepository->updateSupportingDocument($id,$file);
    }

    /**
     * @param int $id
     * @return ProjectBankAccount
     */
    public function restore(int $id): ProjectBankAccount
    {
        try{
            return $this->bankAccountRepository->restore($id);
        }catch(Exception $e){
            abort(409,$e->getMessage());
        }
    }
}
