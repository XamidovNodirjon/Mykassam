<?php

namespace App\Repositories\Eloquent;

use App\Enums\SmsStatus;
use App\Models\SmsMessage;
use App\Repositories\Contracts\SmsMessageRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

    public function awaitingDeliveryReport(CarbonInterface $sentAfter, int $limit): Collection
    {
        return SmsMessage::query()
            ->where('status', SmsStatus::Sent)
            ->whereNotNull('provider_message_id')
            ->where('sent_at', '>=', $sentAfter)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
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
