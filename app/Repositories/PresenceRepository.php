<?php

namespace App\Repositories;

use App\Models\Presence;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *class PresenceRepository
 */
class PresenceRepository
{
    /**
     * @var Presence
     */
    protected $model;

    /**
     * @param Presence $model
     */
    public function __construct(Presence $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->whereHas('personable')
            ->with(['personable', 'task', 'declarer', 'creator']);


        return $query->latest()->paginate($perPage);
    }

    /**
     * @param array $data
     * @return Presence
     */
    public function create(array $data): Presence
    {
        return $this->model->create($data);
    }


    /**
     * @param int $id
     * @return Presence|null
     */
    public function find(int $id): ?Presence
    {
        return $this->model->with(['personable', 'task', 'declarer', 'creator'])->find($id);
    }


    /**
     * @param Presence $presence
     * @param array $data
     * @return bool
     */
    public function update(Presence $presence, array $data): bool
    {
        return $presence->update($data);
    }


    /**
     * @param Presence $presence
     * @return bool
     */
    public function delete(Presence $presence): bool
    {
        return $presence->delete();
    }


    /**
     * @param array $attributes
     * @param array $values
     * @return Presence
     */
    public function updateOrCreate(array $attributes, array $values): Presence
    {
        return $this->model->updateOrCreate($attributes, $values);
    }
}
