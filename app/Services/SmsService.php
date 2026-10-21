<?php

namespace App\Services;

use App\Enums\SmsPurpose;
use App\Enums\SmsStatus;
use App\Exceptions\SmsGatewayException;
use App\Jobs\SendSmsJob;
use App\Models\SmsMessage;
use App\Repositories\Contracts\SmsMessageRepositoryInterface;
use App\Services\Contracts\SmsGatewayInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SmsService
{
    public function __construct(
        private SmsMessageRepositoryInterface $messages,
        private SmsGatewayInterface $gateway,
    ) {}

    /**
     * Log the message and queue its delivery so the request is not blocked by the provider.
     *
     * @param  array<string, string>  $params  Template variables.
     */
    public function send(string $phone, SmsPurpose $purpose, array $params): void
    {
        $message = $this->messages->create([
            'phone' => $phone,
            'purpose' => $purpose,
            'status' => SmsStatus::Pending,
        ]);

        SendSmsJob::dispatch($message, $params);
    }

    /**
     * @return LengthAwarePaginator<int, SmsMessage>
     */
    public function paginate(?string $phone): LengthAwarePaginator
    {
        return $this->messages->paginate($phone);
    }

    /**
     * @return array{total: int, today: int, failed: int}
     */
    public function summary(): array
    {
        return $this->messages->summary();
    }

    /**
     * Provider balance in UZS. When it cannot be read, the amount is null and the error says why.
     *
     * @return array{amount: int|null, error: string|null}
     */
    public function balance(): array
    {
        try {
            return ['amount' => $this->gateway->balance(), 'error' => null];
        } catch (SmsGatewayException $exception) {
            report($exception);

            return ['amount' => null, 'error' => $exception->getMessage()];
        }
    }
}
