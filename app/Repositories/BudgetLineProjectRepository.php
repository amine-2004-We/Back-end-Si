<?php

namespace App\Repositories;

use App\Models\BudgetLineProject;
use Exception;

/**
 * class BudgetLineProjectRepository
 */
class BudgetLineProjectRepository{

    public function withFilters(array $filters){

        $query = BudgetLineProject::query()->with('project', 'partners','budgetLine');



        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data){
        try {
            $budgetLine =  BudgetLineProject::create($data);
            if(!empty($data['partners'])){
                foreach($data['partners'] as $p){
                    $budgetLine->budgetLinePartners()->create([
                        'partner_id' => $p['partner_id'],
                        'allocated_amount' => $p['allocated_amount'],
                    ]);
                }
            }
            return $budgetLine;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update(int $id, array $data)
    {
        try {
            $budgetLine = BudgetLineProject::findOrFail($id);

            $budgetLine->update($data);

            if (!empty($data['partners'])) {
                foreach ($data['partners'] as $p) {
                    $partnerAllocation = $budgetLine->budgetLinePartners()
                        ->where('partner_id', $p['partner_id'])
                        ->first();

                    if ($partnerAllocation) {
                        $partnerAllocation->update([
                            'allocated_amount' => $p['allocated_amount']
                        ]);
                    } else {
                        $budgetLine->budgetLinePartners()->create([
                            'partner_id' => $p['partner_id'],
                            'allocated_amount' => $p['allocated_amount']
                        ]);
                    }
                }
            }

            return $budgetLine->load('budgetLinePartners');

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(int $id){
        try {
            return BudgetLineProject::where('id', $id)->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function bulkDelete(array $ids){
        try {
            return BudgetLineProject::whereIn('id', $ids)->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function restore(int $id){
        try {
            return BudgetLineProject::withTrashed()->where('id', $id)->restore();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /***
     * @param int $budgetLineProjectId
     * @return mixed
     */
    public function getBudgetLinePartners(int $budgetLineProjectId)
    {
        return BudgetLineProject::findOrFail($budgetLineProjectId)
            ->budgetLinePartners;
    }
}
