<?php

namespace App\Http\Requests\Concerns;

use App\Support\PhoneNumber;

/**
 * Brings the "phone" input to the canonical "998XXXXXXXXX" form before validation.
 */
trait NormalizesPhone
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize($this->input('phone')),
        ]);
    }
}
