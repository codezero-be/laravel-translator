<?php

namespace CodeZero\Translator\Tests;

use CodeZero\Translator\TranslatorServiceProvider;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Login in as a user.
     *
     * @return \CodeZero\Translator\Tests\TestCase
     */
    protected function actingAsUser()
    {
        return $this->actingAs(new User());
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
        ];
    }
}
