<?php

namespace App\Providers;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\PlanRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\ShopRepositoryInterface;
use App\Repositories\Contracts\ShopTypeRepositoryInterface;
use App\Repositories\Contracts\SmsMessageRepositoryInterface;
use App\Repositories\Contracts\StatisticsRepositoryInterface;
use App\Repositories\Contracts\StockReceiptRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\DebtRepository;
use App\Repositories\Eloquent\PlanRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\SaleRepository;
use App\Repositories\Eloquent\ShopRepository;
use App\Repositories\Eloquent\ShopTypeRepository;
use App\Repositories\Eloquent\SmsMessageRepository;
use App\Repositories\Eloquent\StatisticsRepository;
use App\Repositories\Eloquent\StockReceiptRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\Contracts\SellerCredentialGeneratorInterface;
use App\Services\SellerCredentialGenerator;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Contract to implementation bindings resolved by the container.
     *
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        UserRepositoryInterface::class => UserRepository::class,
        ShopTypeRepositoryInterface::class => ShopTypeRepository::class,
        ShopRepositoryInterface::class => ShopRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        SaleRepositoryInterface::class => SaleRepository::class,
        SmsMessageRepositoryInterface::class => SmsMessageRepository::class,
        StatisticsRepositoryInterface::class => StatisticsRepository::class,
        PlanRepositoryInterface::class => PlanRepository::class,
        CustomerRepositoryInterface::class => CustomerRepository::class,
        DebtRepositoryInterface::class => DebtRepository::class,
        StockReceiptRepositoryInterface::class => StockReceiptRepository::class,
        SellerCredentialGeneratorInterface::class => SellerCredentialGenerator::class,
    ];
}
