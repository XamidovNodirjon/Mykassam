<?php

use App\Exceptions\BusinessException;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureShopHasFeature;
use App\Http\Middleware\EnsureShopIsSubscribed;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureAccountIsActive::class,
            'subscribed' => EnsureShopIsSubscribed::class,
            'feature' => EnsureShopHasFeature::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Expected rule violations are shown to the user, not logged as errors.
        $exceptions->dontReport(BusinessException::class);

        $exceptions->render(function (BusinessException $exception, Request $request) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return ApiResponse::error($exception->getMessage());
            }

            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        });

        // Show a friendly Inertia page instead of the raw error modal. Server errors keep the
        // debug page locally; 403/404 are user-facing everywhere.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if ($status === 419) {
                Inertia::flash('toast', ['type' => 'error', 'message' => 'Sahifa eskirdi, qaytadan urinib ko‘ring.']);

                return back();
            }

            $userFacing = in_array($status, [403, 404], true)
                || (! app()->environment(['local', 'testing']) && in_array($status, [500, 503], true));

            if (! $userFacing || ($request->expectsJson() && ! $request->header('X-Inertia'))) {
                return $response;
            }

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
