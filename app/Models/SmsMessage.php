<?php

namespace App\Models;

use App\Enums\SmsPurpose;
use App\Enums\SmsStatus;
use Database\Factories\SmsMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Delivery log of an SMS. The verification code itself is never stored; other template variables
 * are kept encrypted until the message is delivered, so a failed one can be sent again.
 *
 * @property int $id
 * @property string $phone
 * @property SmsPurpose $purpose
 * @property array<string, string>|null $params
 * @property SmsStatus $status
 * @property string|null $provider_message_id
 * @property string|null $error
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['phone', 'purpose', 'params', 'status', 'provider_message_id', 'error', 'sent_at'])]
#[Hidden(['params'])]
class SmsMessage extends Model
{
    /**
     * A queued message is only offered for resending after this long, so the queue gets a chance
     * to deliver it first and it is not sent twice.
     */
    public const PENDING_GRACE_SECONDS = 120;

    /** @use HasFactory<SmsMessageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => SmsPurpose::class,
            'params' => 'encrypted:array',
            'status' => SmsStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function markAsSent(?string $providerMessageId): void
    {
        $this->update([
            'status' => SmsStatus::Sent,
            'provider_message_id' => $providerMessageId,
            'params' => null,
            'error' => null,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => SmsStatus::Failed,
            'error' => $error,
        ]);
    }

    /**
     * Why the message cannot be sent again, or null when it can.
     */
    public function resendBlockedReason(): ?string
    {
        if ($this->status === SmsStatus::Sent) {
            return 'SMS allaqachon yuborilgan.';
        }

        if ($this->status === SmsStatus::Pending && $this->updated_at?->gt(now()->subSeconds(self::PENDING_GRACE_SECONDS))) {
            return "SMS hali navbatda. Bir-ikki daqiqadan so'ng qayta urinib ko'ring.";
        }

        if (! $this->purpose->isVerificationCode() && $this->params === null) {
            return "Bu SMS matni saqlanmagan, uni qayta yuborib bo'lmaydi.";
        }

        return null;
    }
}
