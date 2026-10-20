<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    /**
     * Owners manage only their own shops (and the products and sellers inside them).
     */
    public function manage(User $user, Shop $shop): bool
    {
        return $user->isOwner() && $shop->owner_id === $user->id;
    }
}
