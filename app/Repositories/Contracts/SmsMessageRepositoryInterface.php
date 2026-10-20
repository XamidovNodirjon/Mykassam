<?php

namespace App\Repositories\Contracts;

use App\Models\SmsMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * @return array{total: int, today: int, failed: int}
     */
    public function summary(): array;
}
