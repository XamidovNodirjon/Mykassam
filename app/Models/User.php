<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string $login
 * @property string $password
 * @property UserRole $role
 * @property bool $is_active
 * @property int|null $shop_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read Shop|null $shop
 * @property-read int|string|null $revenue Aggregate selected with withSum().
 * @property-read int|string|null $profit Aggregate selected with withSum().
 * @property-read int|string|null $sales_count Aggregate selected with withCount().
 * @property-read int|string|null $last_seen_at Unix time of the latest session request, selected by the admin user list.
 */
#[Fillable(['first_name', 'last_name', 'phone', 'login', 'password', 'role', 'is_active', 'shop_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    /**
     * Shops owned by this user (owner role).
     *
     * @return HasMany<Shop, $this>
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class, 'owner_id');
    }

    /**
     * Shop the seller works in (seller role).
     *
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ofRole(Builder $query, UserRole $role): void
    {
        $query->where('role', $role);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::Owner;
    }

    public function isSeller(): bool
    {
        return $this->role === UserRole::Seller;
    }

    /**
     * Determine whether the account (and for sellers, its shop and owner) may use the system.
     */
    public function hasActiveAccess(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->isSeller()) {
            return true;
        }

        $this->loadMissing('shop.owner');

        return $this->shop !== null
            && $this->shop->is_active
            && $this->shop->owner->is_active;
    }
}
