<?php

namespace App\Services;

use App\Enums\SmsPurpose;
use App\Enums\SmsStatus;
use App\Exceptions\SmsGatewayException;
use App\Models\SmsMessage;
use App\Repositories\Contracts\SmsMessageRepositoryInterface;
use App\Services\Contracts\SmsGatewayInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SmsService
{
    /**
     * Delivery reports are only looked up for messages sent within this many hours.
     */
    private const DELIVERY_REPORT_WINDOW_HOURS = 72;

    /**
     * At most this many messages are checked per sync, to keep it quick.
     */
    private const DELIVERY_REPORT_BATCH = 50;

    public function __construct(
        private SmsMessageRepositoryInterface $messages,
        private SmsGatewayInterface $gateway,
    ) {}

    /**
     * Log the message and hand it to the provider right away: a person waiting for a code needs
     * it now. The log entry ends up "sent" (accepted, delivery not yet confirmed) or "failed".
     *
     * @param  array<string, string>  $params  Template variables.
     *
     * @throws SmsGatewayException When the provider does not accept the message.
     */
    public function send(string $phone, SmsPurpose $purpose, array $params): void
    {
        $message = $this->messages->create([
            'phone' => $phone,
            'purpose' => $purpose,
            'status' => SmsStatus::Pending,
        ]);

        try {
            $message->markAsSent($this->gateway->send($phone, $purpose, $params, (string) Str::uuid()));
        } catch (SmsGatewayException $exception) {
            $message->markAsFailed($exception->getMessage());

            throw $exception;
        }
    }

    /**
     * Ask the provider whether recently sent messages reached the phone and record the outcome.
     *
     * @return int Number of messages whose status changed.
     */
    public function syncDeliveryReports(): int
    {
        $changed = 0;
        $pending = $this->messages->awaitingDeliveryReport(
            now()->subHours(self::DELIVERY_REPORT_WINDOW_HOURS),
            self::DELIVERY_REPORT_BATCH,
        );

        foreach ($pending as $message) {
            try {
                $report = $this->gateway->deliveryReport((string) $message->provider_message_id);
            } catch (SmsGatewayException $exception) {
                report($exception);

                continue;
            }

            if ($report->status === SmsStatus::Delivered) {
                $message->markAsDelivered();
                $changed++;
            } elseif ($report->status === SmsStatus::Failed) {
                $message->markAsFailed('Operator yetkazmadi'.($report->note !== null ? ": {$report->note}" : '.'));
                $changed++;
            }
        }

        return $changed;
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
