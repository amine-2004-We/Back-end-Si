<?php

namespace App\Repositories;

use App\Http\Resources\ProjectBankAccountResource;
use App\Models\ProjectBankAccount;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * class ProjectBankAccountRepository
 */
class ProjectBankAccountRepository
{
    /**
     * @return AnonymousResourceCollection
     */
    public function all(): AnonymousResourceCollection
    {
        $bankAccounts = ProjectBankAccount::paginate(10);

        return ProjectBankAccountResource::collection($bankAccounts);
    }

    /**
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator|mixed
     */
    public function allWithFilters(array $filters)
    {
        $query = ProjectBankAccount::query()
                ->orderByDesc('created_at')
                ->orderByRaw('deleted_at is Not NULL')
                ->with('bank');

        if(!empty($filters['rib_iban'])){
            $query->where('rib_iban', 'like', '%'.$filters['rib_iban'].'%');
        }

        if(!empty($filters['agency'])){
            $query->where('agency', '=', $filters['agency']);
        }

        if(!empty($filters['bank'])){
            $query->where('bank', '=', $filters['bank']);
        }

        if(!empty($filters['account_title'])){
            $query->where('account_title', 'like', '%'.$filters['account_title'].'%');
        }

        if(!empty($filters['account_holder_name'])){
            $query->where('account_holder_name', 'like', '%'.$filters['account_holder_name'].'%');
        }

        if(!empty($filters['status'])){
            $query->where('status', '=', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 10;

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

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination()
    {
        return ProjectBankAccount::all('id','account_holder_name','account_title','rib_iban');
    }

    /**
     * @param int $id
     * @return ProjectBankAccount
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProjectBankAccount
    {
        return ProjectBankAccount::with('bank')->findOrFail($id);
    }

    /**
     * @param int $id
     * @return ProjectBankAccount
     */
    public function findWithTrashed(int $id): ProjectBankAccount
    {
        return ProjectBankAccount::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @param string|null $fileName
     * @return ProjectBankAccount
     */
    public function create(array $data,?string $fileName): ProjectBankAccount
    {
        if($fileName){
            $data['supporting_document'] = $fileName;
        }
        $bankAccount = new ProjectBankAccount($data);
        $auth = auth()->user()->id;

        if($auth){
            $bankAccount->createdBy()->associate($auth);
            $bankAccount->save();

            return $bankAccount;
        }else{
            throw new ModelNotFoundException();
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @return ProjectBankAccount
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): ProjectBankAccount
    {
        $account = $this->find($id);
        $account->update($data);

        return $account;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $account = $this->find($id);
        return $account->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return ProjectBankAccount::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @param string|null $path
     */
    public function updateSupportingDocument(int $id, ?string $path): void
    {
        $account = $this->find($id);
        if($path){
            $account->supporting_document = $path;
        }else{
            $account->supporting_document = null;
        }
        $account->update();
    }

    /**
     * @param int $id
     * @return ProjectBankAccount
     */
   public function restore(int $id): ProjectBankAccount
    {
        $bankAccount = $this->findWithTrashed($id);

        $ribExists = ProjectBankAccount::where('rib_iban', $bankAccount->rib_iban)
            ->whereNull('deleted_at')
            ->exists();

        $accountTitleExists = ProjectBankAccount::where('account_title', $bankAccount->account_title)
            ->whereNull('deleted_at')
            ->exists();

        $conflicts = [];

        if ($ribExists) {
            $conflicts[] = "RIB/IBAN";
        }

        if ($accountTitleExists) {
            $conflicts[] = "nom du compte";
        }

        if (!empty($conflicts)) {
            $fields = implode(" et ", $conflicts);
            abort(Response::HTTP_CONFLICT, "Impossible de restaurer : les champs suivants sont déjà utilisés par d'autres comptes actifs : $fields.");
        }

        $bankAccount->restore();

        return $bankAccount;
    }
}
