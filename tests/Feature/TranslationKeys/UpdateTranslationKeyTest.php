<?php

namespace CodeZero\Translator\Tests\Feature\TranslationKeys;

use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\TranslatorRoutes;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateTranslationKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_a_translation_key()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'is_html' => false,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'is_html' => true,
            'key' => 'new-key',
            'translations' => [
                'en' => 'new translation [en]',
            ],
        ]);

        $response->assertSuccessful();

        $keys = $file->translationKeys()->get();
        $this->assertCount(1, $keys);
        $this->assertTrue($keys->first()->is($key));
        $this->assertTrue($keys->first()->isHtml());
        $this->assertEquals('new-key', $keys->first()->key);
        $this->assertEquals([
            'en' => 'new translation [en]',
        ], $keys->first()->translations);
    }

    /** @test */
    public function it_returns_the_updated_translation_key()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'is_html' => false,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'is_html' => true,
            'key' => 'new-key',
            'translations' => [
                'en' => 'new translation [en]',
            ],
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response->original->is($key));
        $this->assertTrue($response->original->isHtml());
        $this->assertEquals('new-key', $response->original->key);
        $this->assertEquals([
            'en' => 'new translation [en]',
        ], $response->original->translations);
    }

    /** @test */
    public function is_html_can_be_omitted()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'is_html' => true,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'key' => 'new-key',
            'translations' => [
                'en' => 'new translation [en]',
                'nl' => 'new translation [nl]',
            ],
        ]);

        $response->assertSuccessful();

        $keys = $file->translationKeys()->get();
        $this->assertCount(1, $keys);
        $this->assertTrue($keys->first()->is($key));
        $this->assertTrue($keys->first()->isHtml());
        $this->assertEquals('new-key', $keys->first()->key);
        $this->assertEquals([
            'en' => 'new translation [en]',
            'nl' => 'new translation [nl]',
        ], $keys->first()->translations);
    }

    /** @test */
    public function is_html_should_be_a_boolean()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'existing-key',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'is_html' => 'invalid',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_html');

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'is_html' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_html');
    }

    /** @test */
    public function translation_key_can_be_omitted()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'is_html' => false,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'is_html' => true,
            'translations' => [
                'en' => 'new translation [en]',
                'nl' => 'new translation [nl]',
            ],
        ]);

        $response->assertSuccessful();

        $keys = $file->translationKeys()->get();
        $this->assertCount(1, $keys);
        $this->assertTrue($keys->first()->is($key));
        $this->assertTrue($keys->first()->isHtml());
        $this->assertEquals('existing-key', $keys->first()->key);
        $this->assertEquals([
            'en' => 'new translation [en]',
            'nl' => 'new translation [nl]',
        ], $keys->first()->translations);
    }

    /** @test */
    public function translation_key_should_not_be_null()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'existing-key',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'is_html' => 'invalid',
            'key' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');
    }

    /** @test */
    public function translation_key_should_be_unique_within_the_same_translation_file()
    {
        TranslatorRoutes::register();

        $currentFile = TranslationFile::create(['filename' => 'test-file-a']);
        $otherFile = TranslationFile::create(['filename' => 'test-file-b']);

        $currentKey = TranslationKey::create([
            'file_id' => $currentFile->id,
            'key' => 'some.nested.current-key',
        ]);

        TranslationKey::create([
            'file_id' => $otherFile->id,
            'key' => 'some.nested.existing-key',
        ]);

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.current-key',
        ])->assertSuccessful();

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.current-key.can-go-deeper',
        ])->assertSuccessful();

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.existing-key',
        ])->assertSuccessful();

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.new-key',
        ])->assertSuccessful();

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested',
        ])->assertSuccessful();

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some',
        ])->assertSuccessful();
    }

    /** @test */
    public function translation_key_can_not_be_updated_to_a_namespace_if_it_is_still_in_use()
    {
        TranslatorRoutes::register();

        $currentFile = TranslationFile::create(['filename' => 'test-file']);

        $currentKey = TranslationKey::create([
            'file_id' => $currentFile->id,
            'key' => 'some.nested.current-key',
        ]);

        TranslationKey::create([
            'file_id' => $currentFile->id,
            'key' => 'some.nested.existing-key',
        ]);

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.current-key',
        ])->assertSuccessful();

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.existing-key',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.existing-key.can-go-deeper',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'some.nested.new-key',
        ])->assertSuccessful();
    }

    /** @test */
    public function translation_key_should_be_unique_within_the_same_json_file()
    {
        TranslatorRoutes::register();

        $currentFile = TranslationFile::create(['filename' => '_json']);

        $currentKey = TranslationKey::create([
            'file_id' => $currentFile->id,
            'key' => 'Some string key. This one is the current one!',
        ]);

        TranslationKey::create([
            'file_id' => $currentFile->id,
            'key' => 'Some string key. This one exists!',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'Some string key. This one exists!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'Some string key. This one is the current one!',
        ])->assertSuccessful();

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'Some string key. This one exists! Not!',
        ])->assertSuccessful();

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$currentKey]), [
            'key' => 'Some string key',
        ])->assertSuccessful();
    }

    /** @test */
    public function translation_key_may_not_start_or_end_with_a_dot()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'some-key',
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'key' => 'some-key.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'key' => '.some-key',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');
    }

    /** @test */
    public function translation_key_may_start_or_end_with_a_dot_in_json_files()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => '_json']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'Some string key',
        ]);

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'key' => '.Some string key.',
        ])->assertSuccessful();
    }

    /** @test */
    public function translations_can_be_omitted()
    {
        $this->withoutExceptionHandling();

        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'is_html' => false,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'is_html' => true,
            'key' => 'new-key',
        ]);

        $response->assertSuccessful();

        $keys = $file->translationKeys()->get();
        $this->assertCount(1, $keys);
        $this->assertTrue($keys->first()->is($key));
        $this->assertTrue($keys->first()->isHtml());
        $this->assertEquals('new-key', $keys->first()->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => 'existing translation [nl]',
        ], $keys->first()->translations);
    }

    /** @test */
    public function translations_should_be_an_array()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'translations' => 'not-an-array',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('translations');

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'translations' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('translations');

        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => 'existing translation [nl]',
        ], $key->fresh()->translations);
    }

    /** @test */
    public function translation_values_should_be_a_string()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $response = $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'translations' => [
                'en' => [
                    'nope' => 'no can do',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('translations.en');
    }

    /** @test */
    public function translation_values_can_be_empty()
    {
        TranslatorRoutes::register();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $key = TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $this->actingAsUser()->patchJson(route('translator.keys.update', [$key]), [
            'translations' => [
                'en' => null,
            ],
        ])->assertSuccessful();
    }
}
