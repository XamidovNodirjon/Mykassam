<?php

namespace App\Repositories\Contracts;

use App\Models\SmsMessage;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SmsMessageRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SmsMessage;

    /**
     * @return LengthAwarePaginator<int, SmsMessage>
     */
    public function paginate(?string $phone, int $perPage = 20): LengthAwarePaginator;

    /**
     * Messages the provider accepted recently whose delivery is not confirmed yet, newest first.
     *
     * @return Collection<int, SmsMessage>
     */
    public function awaitingDeliveryReport(CarbonInterface $sentAfter, int $limit): Collection;

    /**
     * @return array{total: int, today: int, failed: int}
     */
    public function summary(): array;
}
