<?php

namespace App\Http\Middleware\Concerns;

use App\Models\Shop;
use Illuminate\Http\Request;

/**
 * The shop a request works in: the {shop} route parameter (owner) or the seller's own shop.
 */
trait ResolvesCurrentShop
{
    protected function currentShop(Request $request): ?Shop
    {
        $shop = $request->route('shop') ?? $request->user()?->loadMissing('shop')->shop;

        return $shop instanceof Shop ? $shop : null;
    }
}
