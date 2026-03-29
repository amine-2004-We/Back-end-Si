<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * class UserService
 */
class UserService
{
    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->userRepository->query()->get();
    }

    /**
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return $this->userRepository->allWithoutPagination();
    }
}
