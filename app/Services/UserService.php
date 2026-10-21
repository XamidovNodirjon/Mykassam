<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Overview of every account in the system for the administrator.
 */
class UserService
{
    /**
     * Someone who made a request this recently counts as online.
     */
    public const ONLINE_WITHIN_SECONDS = 300;

    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(?string $search, ?UserRole $role): LengthAwarePaginator
    {
        return $this->users->paginateAll($search, $role);
    }

    /**
     * @return array{total: int, owners: int, sellers: int, blocked: int, online: int}
     */
    public function summary(): array
    {
        return $this->users->summary(self::ONLINE_WITHIN_SECONDS);
    }
}
