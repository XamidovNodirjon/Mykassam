<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => function () use ($request): ?array {
                    $user = $request->user();

                    if ($user === null) {
                        return null;
                    }

                    if ($user->isSeller()) {
                        $user->loadMissing('shop:id,name');
                    }

                    return UserResource::make($user)->resolve($request);
                },
            ],
            'support' => [
                'telegram' => config('subscription.contact_telegram'),
                'phone' => config('subscription.contact_phone'),
            ],
            'subscription' => fn (): ?array => $request->user()
                ? app(SubscriptionService::class)->noticeFor($request->user())
                : null,
        ];
    }
}
