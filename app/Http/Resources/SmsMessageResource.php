<?php

namespace App\Http\Resources;

use App\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SmsMessage
 */
class SmsMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'purpose_label' => $this->purpose->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'error' => $this->error,
            'can_resend' => $this->resendBlockedReason() === null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
