<?php

namespace App\Repositories\Eloquent;

use App\Enums\SmsStatus;
use App\Models\SmsMessage;
use App\Repositories\Contracts\SmsMessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SmsMessageRepository implements SmsMessageRepositoryInterface
{
    public function create(array $attributes): SmsMessage
    {
        return SmsMessage::query()->create($attributes);
    }

    public function paginate(?string $phone, int $perPage = 20): LengthAwarePaginator
    {
        return SmsMessage::query()
            ->when($phone, fn (Builder $query, string $phone) => $query->where('phone', 'like', "%{$phone}%"))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summary(): array
    {
        return [
            'total' => SmsMessage::query()->count(),
            'today' => SmsMessage::query()->where('created_at', '>=', today())->count(),
            'failed' => SmsMessage::query()->where('status', SmsStatus::Failed)->count(),
        ];
    }
}
