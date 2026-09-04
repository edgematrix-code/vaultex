<?php

namespace Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the application, pinning the test environment and an isolated
     * in-memory SQLite database before the testing traits (e.g. RefreshDatabase)
     * run their migrations.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        // `php artisan test` boots the application (and therefore loads .env)
        // before PHPUnit applies its environment variables, so pin the testing
        // environment explicitly to keep CSRF / rate-limit behavior deterministic.
        $app['env'] = 'testing';

        // Tests always run against an isolated in-memory SQLite database and
        // must never touch the configured development MySQL database.
        $config = $app['config'];
        $config->set('database.default', 'sqlite');
        $config->set('database.connections.sqlite.database', ':memory:');

        // Pin the remaining infrastructure to non-persistent drivers.
        $config->set('cache.default', 'array');
        $config->set('queue.default', 'sync');
        $config->set('mail.default', 'array');
        $config->set('session.driver', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Rate limiters (and anything else backed by the array cache) would
        // otherwise accumulate state across tests sharing this process.
        $this->app['cache']->flush();
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
