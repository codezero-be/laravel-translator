<?php

namespace CodeZero\Translator\Tests\Concerns;

use Orchestra\Testbench\Exceptions\Handler;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;

trait DisablesExceptionHandling
{
    /**
     * Disable Laravel's exception handling.
     *
     * @return $this
     */
    protected function withoutExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(Exception $e) {}
            public function render($request, Exception $e) {
                throw $e;
            }
        });

        return $this;
    }
}
