<?php

declare(strict_types=1);

namespace WeBRTeu\EuPlatesc;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use WeBRTeu\Payments\PaymentManager;

class EuPlatescServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/euplatesc.php', 'euplatesc');

        $this->app->singleton(EuPlatescGateway::class);

        $this->callAfterResolving(PaymentManager::class, function (PaymentManager $payments): void {
            $payments->extend('euplatesc', fn (Container $app): EuPlatescGateway => $app->make(EuPlatescGateway::class));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/euplatesc.php' => $this->app->configPath('euplatesc.php'),
        ], 'euplatesc-config');
    }
}
