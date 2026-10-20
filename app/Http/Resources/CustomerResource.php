<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'bonus_balance' => $this->whenHas('bonus_balance'),
            'debt' => $this->whenHas('debt_amount', fn (): int => (int) $this->debt_amount - (int) $this->debt_paid),
        ];
    }
}
