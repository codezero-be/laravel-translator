<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Routes\TranslatorRoutes;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_users_can_ping_the_keep_alive_route_by_default()
    {
        TranslatorRoutes::register();

        $this->getJson(route('translator.keep.alive'))->assertUnauthorized();
        $this->actingAsUser()->getJson(route('translator.keep.alive'))->assertSuccessful();
    }

    /** @test */
    public function only_users_can_import_and_export_translation_files_by_default()
    {
        TranslatorRoutes::register();

        $this->postJson(route('translator.import'))->assertUnauthorized();
        $this->postJson(route('translator.export'))->assertUnauthorized();

        $this->actingAsUser()->postJson(route('translator.import'))->assertSuccessful();
        $this->actingAsUser()->postJson(route('translator.export'))->assertSuccessful();
    }

    /** @test */
    public function only_users_can_manage_translation_files_by_default()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'filename' => 'test-file',
        ]);

        $this->getJson(route('translator.files.index'))->assertUnauthorized();
        $this->postJson(route('translator.files.store'))->assertUnauthorized();
        $this->patchJson(route('translator.files.update', [$file]))->assertUnauthorized();
        $this->deleteJson(route('translator.files.destroy', [$file]))->assertUnauthorized();

        $this->actingAsUser()->getJson(route('translator.files.index'))->assertSuccessful();
        $this->actingAsUser()->postJson(route('translator.files.store'))->assertStatus(422);
        $this->actingAsUser()->patchJson(route('translator.files.update', [$file]))->assertStatus(422);
        $this->actingAsUser()->deleteJson(route('translator.files.destroy', [$file]))->assertSuccessful();
    }

    /** @test */
    public function only_users_can_manage_translation_keys_by_default()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create([
            'filename' => 'test-file',
        ]);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'test-key',
        ]);

        $this->getJson(route('translator.keys.index', [$file]))->assertUnauthorized();
        $this->postJson(route('translator.keys.store', [$file]))->assertUnauthorized();
        $this->patchJson(route('translator.keys.update', [$key]))->assertUnauthorized();
        $this->deleteJson(route('translator.keys.destroy', [$key]))->assertUnauthorized();

        $this->actingAsUser()->getJson(route('translator.keys.index', [$file]))->assertSuccessful();
        $this->actingAsUser()->postJson(route('translator.keys.store', [$file]))->assertStatus(422);
        $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]))->assertSuccessful();
        $this->actingAsUser()->deleteJson(route('translator.keys.destroy', [$key]))->assertSuccessful();
    }
}
