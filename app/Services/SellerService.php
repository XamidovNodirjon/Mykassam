<?php

namespace App\Services;

use App\DTOs\CredentialsData;
use App\DTOs\SellerData;
use App\Enums\UserRole;
use App\Exceptions\BusinessException;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\SellerCredentialGeneratorInterface;
use Illuminate\Database\Eloquent\Collection;

class SellerService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private SellerCredentialGeneratorInterface $credentials,
        private ShopPlanGuard $guard,
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function listForShop(Shop $shop): Collection
    {
        return $this->users->sellersOfShop($shop);
    }

    /**
     * @param  list<int>  $shopIds
     * @return Collection<int, User>
     */
    public function optionsForShops(array $shopIds): Collection
    {
        return $this->users->sellersOfShops($shopIds);
    }

    public function suggestCredentials(string $phone, ?int $exceptSellerId = null): CredentialsData
    {
        return $this->credentials->generate($phone, $exceptSellerId);
    }

    /**
     * Create the seller and return the plain credentials so the owner can hand them over once.
     */
    public function create(Shop $shop, SellerData $data): CredentialsData
    {
        $this->guard->ensureCanAddSeller($shop);

        $password = $data->password ?? $this->credentials->generatePassword();

        $this->users->create([
            ...$data->toArray(),
            'password' => $password,
            'role' => UserRole::Seller,
            'shop_id' => $shop->id,
        ]);

        return new CredentialsData($data->login, $password);
    }

    /**
     * Update the seller. Returns new credentials only when the password was changed.
     */
    public function update(User $seller, SellerData $data): ?CredentialsData
    {
        $this->users->update($seller, $data->toArray());

        return $data->password !== null
            ? new CredentialsData($data->login, $data->password)
            : null;
    }

    /**
     * @throws BusinessException
     */
    public function delete(User $seller): void
    {
        if ($this->users->hasSales($seller)) {
            throw new BusinessException("Sotuvchining savdolari bor. O'chirish o'rniga nofaol qiling.");
        }

        $this->users->delete($seller);
    }
}
