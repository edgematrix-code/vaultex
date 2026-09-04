<?php

namespace App\Providers;

use App\Listeners\UpdateLastLogin;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureListeners();
        $this->configureRateLimiting();
    }

    /**
     * Register application event listeners.
     */
    protected function configureListeners(): void
    {
        Event::listen(Login::class, UpdateLastLogin::class);
    }

    /**
     * Configure named application rate limiters.
     */
    protected function configureRateLimiting(): void
    {
        // Throttle these actions per account rather than per IP address.
        RateLimiter::for('password-update', function (Request $request) {
            return $this->throttledLimit($request, 6);
        });

        RateLimiter::for('verification', function (Request $request) {
            return $this->throttledLimit($request, 6);
        });
    }

    /**
     * Build a stable per-account limit (IP when unauthenticated).
     *
     * Rate limiting is disabled while running the test suite, where every
     * user row is rolled back and re-created with the same primary key,
     * which would otherwise accumulate attempts across tests.
     */
    private function throttledLimit(Request $request, int $maxAttempts): Limit
    {
        if (app()->environment('testing')) {
            return Limit::none();
        }

        $user = $request->user();
        $key = $user !== null ? (string) $user->id : (string) $request->ip();

        return Limit::perMinute($maxAttempts)->by($key);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
