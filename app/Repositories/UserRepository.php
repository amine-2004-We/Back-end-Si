<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserRepository
 */
class UserRepository
{
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return User::query();
    }

    public function allWithoutPagination()
    {
        return User::all('id', 'name', 'email');
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * @param int $id
     * @return User
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findOrFail($id);
        return $user->update($data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $user = $this->findOrFail($id);
        return $user->delete();
    }
}
