<?php

namespace App\Http\Middleware;

use App\Exceptions\BusinessException;
use App\Http\Middleware\Concerns\ResolvesCurrentShop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks every change in a shop whose subscription has expired. Pages still open so the
 * payment notice can be shown.
 */
class EnsureShopIsSubscribed
{
    use ResolvesCurrentShop;

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $shop = $this->currentShop($request);

        if ($shop !== null && ! $shop->hasActiveSubscription()) {
            throw new BusinessException("\"{$shop->name}\" do'konining obunasi tugagan. Davom etish uchun to'lovni amalga oshiring.");
        }

        return $next($request);
    }
}
