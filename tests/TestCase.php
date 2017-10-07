<?php

namespace CodeZero\Translator\Tests;

use CodeZero\Translator\TranslatorServiceProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Translatable\TranslatableServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        config()->set('app.key', str_random(32));

        $this->artisan('migrate', ['--database' => 'testing']);

        $this->withFactories(__DIR__.'/../database/factories');

        $this->be(new User());
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            TranslatorServiceProvider::class,
            TranslatableServiceProvider::class,
        ];
    }
}
