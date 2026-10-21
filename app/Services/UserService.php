<?php

namespace App\Services;

use App\DTOs\AdminUserData;
use App\Enums\UserRole;
use App\Exceptions\BusinessException;
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

    /**
     * Owners sign in with their phone, so changing it changes their login too. Administrators
     * stay active, so nobody can lock the panel.
     */
    public function update(User $user, AdminUserData $data): User
    {
        $attributes = [
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'phone' => $data->phone,
            'login' => $user->role === UserRole::Owner ? $data->phone : $data->login,
            'is_active' => $user->role === UserRole::Admin ? true : $data->isActive,
        ];

        if ($data->password !== null) {
            $attributes['password'] = $data->password;
        }

        return $this->users->update($user, $attributes);
    }

    /**
     * @throws BusinessException When the account belongs to an administrator.
     */
    public function delete(User $user): void
    {
        match ($user->role) {
            UserRole::Admin => throw new BusinessException("Administrator hisobini o'chirib bo'lmaydi."),
            UserRole::Owner => $this->users->deleteOwnerWithShops($user),
            UserRole::Seller => $this->users->deleteSellerWithSales($user),
        };
    }
}
