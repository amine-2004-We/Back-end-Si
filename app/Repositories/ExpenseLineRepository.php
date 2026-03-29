<?php

namespace App\Repositories;

use App\Models\ExpenseLine;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExpenseLineRepository
{
    protected ExpenseLine $model;

    public function __construct(ExpenseLine $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ExpenseLine
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): ExpenseLine
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ExpenseLine
    {
        $line = $this->find($id);
        $line->update($data);
        return $line;
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }
}