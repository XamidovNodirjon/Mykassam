<?php

namespace App\Http\Resources;

use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Debt
 */
class DebtResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'sale_id' => $this->sale_id,
            'amount' => $this->amount,
            'paid_amount' => $this->paid_amount,
            'remaining' => $this->remaining(),
            'due_date' => $this->due_date->toDateString(),
            'status' => $this->status()->value,
            'status_label' => $this->status()->label(),
            'note' => $this->note,
            'reminded_at' => $this->reminded_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
