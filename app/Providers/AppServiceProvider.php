<?php

namespace App\Providers;

use App\Services\Contracts\SmsGatewayInterface;
use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\UsmsGateway;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsGatewayInterface::class, fn (Application $app): SmsGatewayInterface => match (config('sms.driver')) {
            'usms' => $app->make(UsmsGateway::class),
            default => $app->make(LogSmsGateway::class),
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Throttle sign-in and sign-up attempts per login and IP address, and SMS requests
     * per phone and IP address so nobody can drain the SMS balance.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(mb_strtolower((string) $request->input('login', $request->input('phone'))).'|'.$request->ip()));

        RateLimiter::for('sms', fn (Request $request): array => [
            Limit::perHour(5)->by(PhoneNumber::normalize($request->input('phone')).'|'.$request->ip()),
            Limit::perHour(20)->by($request->ip()),
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        JsonResource::withoutWrapping();

        Model::preventLazyLoading(! app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // Shop staff type passwords on tills and phones: a short minimum, no character classes.
        Password::defaults(fn (): Password => Password::min(6)->max(64));
    }
}
