<?php

namespace App\Http\Middleware;

use App\Enums\PlanFeature;
use App\Http\Middleware\Concerns\ResolvesCurrentShop;
use App\Services\ShopPlanGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('feature:debts'). Changes need the module in the shop's plan; pages stay
 * open so data kept from a higher plan remains readable after a downgrade.
 */
class EnsureShopHasFeature
{
    use ResolvesCurrentShop;

    public function __construct(private ShopPlanGuard $guard) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $shop = $this->currentShop($request);

        if (! $request->isMethodSafe() && $shop !== null) {
            $this->guard->ensureFeature($shop, PlanFeature::from($feature));
        }

        return $next($request);
    }
}
