<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Sellers see only their own sales; owners see sales of their shops.
     */
    public function view(User $user, Sale $sale): bool
    {
        if ($user->isSeller()) {
            return $sale->seller_id === $user->id;
        }

        if ($user->isOwner()) {
            return $user->shops()->whereKey($sale->shop_id)->exists();
        }

        return false;
    }
}
