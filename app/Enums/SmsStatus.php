<?php

namespace App\Enums;

/**
 * Lifecycle of an SMS: the provider accepting it ("sent") does not mean it reached the phone;
 * only the delivery report turns it into "delivered" or "failed".
 */
enum SmsStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Yuborilmoqda',
            self::Sent => 'Operatorga uzatildi',
            self::Delivered => 'Yetkazildi',
            self::Failed => 'Xatolik',
        };
    }
}
