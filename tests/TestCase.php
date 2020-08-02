<?php

namespace CodeZero\Translator\Tests;

use CodeZero\Translator\TranslatorServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
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
