<?php

namespace App\Models;

use App\Enums\SmsPurpose;
use App\Enums\SmsStatus;
use Database\Factories\SmsMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Delivery log of an SMS. The verification code itself is never stored.
 *
 * @property int $id
 * @property string $phone
 * @property SmsPurpose $purpose
 * @property SmsStatus $status
 * @property string|null $provider_message_id
 * @property string|null $error
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['phone', 'purpose', 'status', 'provider_message_id', 'error', 'sent_at'])]
class SmsMessage extends Model
{
    /** @use HasFactory<SmsMessageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => SmsPurpose::class,
            'status' => SmsStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function markAsSent(?string $providerMessageId): void
    {
        $this->update([
            'status' => SmsStatus::Sent,
            'provider_message_id' => $providerMessageId,
            'error' => null,
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update(['status' => SmsStatus::Delivered, 'error' => null]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => SmsStatus::Failed,
            'error' => $error,
        ]);
    }
}
