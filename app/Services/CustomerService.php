<?php

namespace App\Services;

use App\DTOs\CustomerData;
use App\Exceptions\BusinessException;
use App\Models\Customer;
use App\Models\Shop;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    public function __construct(private CustomerRepositoryInterface $customers) {}

    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(Shop $shop, ?string $search): LengthAwarePaginator
    {
        return $this->customers->paginateForShop($shop, $search);
    }

    /**
     * @return Collection<int, Customer>
     */
    public function search(Shop $shop, string $search): Collection
    {
        return $this->customers->searchForShop($shop->id, $search);
    }

    /**
     * @return Collection<int, Customer>
     */
    public function options(Shop $shop): Collection
    {
        return $this->customers->optionsForShop($shop);
    }

    public function create(Shop $shop, CustomerData $data): Customer
    {
        return $this->customers->create($shop, $data->toArray());
    }

    public function update(Customer $customer, CustomerData $data): Customer
    {
        return $this->customers->update($customer, $data->toArray());
    }

    /**
     * @throws BusinessException
     */
    public function delete(Customer $customer): void
    {
        if ($this->customers->hasDebts($customer)) {
            throw new BusinessException("Mijozning qarzlar tarixi bor, uni o'chirib bo'lmaydi.");
        }

        $this->customers->delete($customer);
    }
}
