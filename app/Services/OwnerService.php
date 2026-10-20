<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OwnerService
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->users->paginateOwners($search);
    }

    /**
     * Blocking an owner also blocks their sellers (checked on every request).
     */
    public function toggleStatus(User $owner): User
    {
        return $this->users->update($owner, ['is_active' => ! $owner->is_active]);
    }
}
