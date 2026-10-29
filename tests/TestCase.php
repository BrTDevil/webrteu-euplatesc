<?php

declare(strict_types=1);

namespace WeBRTeu\EuPlatesc\Tests;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\URL;
use Orchestra\Testbench\TestCase as Orchestra;
use WeBRTeu\EuPlatesc\EuPlatescServiceProvider;
use WeBRTeu\Payments\PaymentsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // URL-urile generate (notifyUrl, BACKREF...) folosesc app.url, nu host-ul cererii de test.
        URL::forceRootUrl('https://shop.example');
        URL::forceScheme('https');
    }

    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [PaymentsServiceProvider::class, EuPlatescServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $app['config']->set('app.url', 'https://shop.example');
        $app['config']->set('euplatesc.merchant_id', env('EUPLATESC_SANDBOX_MERCHANT_ID', '44840981287'));
        $app['config']->set('euplatesc.secret_key', env('EUPLATESC_SANDBOX_SECRET_KEY', '00112233445566778899AABBCCDDEEFF'));
    }

    protected function defineDatabaseMigrations(): void
    {
        /** @var Migration $migration */
        $migration = require __DIR__.'/../vendor/webrteu/payments/database/migrations/create_payments_tables.php.stub';
        $migration->up();
    }
}
