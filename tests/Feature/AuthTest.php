<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Routes\TranslatorRoutes;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_can_not_manage_translations()
    {
        TranslatorRoutes::register();

        $this->postJson(route('translator.import'))->assertUnauthorized();
        $this->postJson(route('translator.export'))->assertUnauthorized();
    }

    /** @test */
    public function users_can_manage_translations_by_default()
    {
        TranslatorRoutes::register();

        $this->actingAsUser()->postJson(route('translator.import'))->assertSuccessful();
        $this->actingAsUser()->postJson(route('translator.export'))->assertSuccessful();
    }
}
