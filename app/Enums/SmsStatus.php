<?php

namespace App\Enums;

enum SmsStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Yuborilmoqda',
            self::Sent => 'Yuborildi',
            self::Failed => 'Xatolik',
        };
    }
}
