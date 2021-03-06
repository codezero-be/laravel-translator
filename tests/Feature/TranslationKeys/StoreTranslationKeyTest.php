<?php

namespace CodeZero\Translator\Tests\Feature\TranslationKeys;

use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTranslationKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_a_translation_key_to_a_translation_file()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'is_html' => true,
            'key' => 'some.key',
            'translations' => [
                'en' => 'some value',
            ],
        ]);

        $response->assertSuccessful();

        $keys = $file->keys()->get();
        $this->assertCount(1, $keys);
        $this->assertTrue($keys->first()->isHtml());
        $this->assertEquals('some.key', $keys->first()->key);
        $this->assertEquals([
            'en' => 'some value',
        ], $keys->first()->translations);
    }

    /** @test */
    public function it_returns_the_new_translation_key()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'is_html' => true,
            'key' => 'some.key',
            'translations' => [
                'en' => 'some value',
            ],
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response->original->isHtml());
        $this->assertEquals('some.key', $response->original->key);
        $this->assertEquals([
            'en' => 'some value',
        ], $response->original->translations);
    }

    /** @test */
    public function is_html_can_be_omitted()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'some.key',
        ]);

        $response->assertSuccessful();

        $keys = $file->keys()->get();
        $this->assertCount(1, $keys);
        $this->assertFalse($keys->first()->isHtml());
    }

    /** @test */
    public function is_html_should_be_a_boolean()
    {
        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'is_html' => 'invalid',
            'key' => 'some.key',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_html');

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'is_html' => null,
            'key' => 'some.key',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_html');
    }

    /** @test */
    public function translation_key_is_required()
    {
        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');
    }

    /** @test */
    public function translation_key_should_be_unique_within_the_same_translation_file()
    {
        $fileA = TranslationFile::create(['filename' => 'test-file-a']);
        $fileB = TranslationFile::create(['filename' => 'test-file-b']);

        TranslationKey::create([
            'file_id' => $fileA->id,
            'key' => 'some.nested.existing-key',
        ]);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$fileA]), [
            'key' => 'some',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$fileA]), [
            'key' => 'some.nested',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$fileA]), [
            'key' => 'some.nested.existing-key',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$fileA]), [
            'key' => 'some.nested.existing-key.can-not-go-deeper',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $this->actingAsUser()->postJson(route('translator.keys.store', [$fileA]), [
            'key' => 'some.nested.new-key',
        ])->assertSuccessful();

        $this->actingAsUser()->postJson(route('translator.keys.store', [$fileB]), [
            'key' => 'some.nested.existing-key',
        ])->assertSuccessful();
    }

    /** @test */
    public function translation_key_should_be_unique_within_the_same_json_file()
    {
        $file = TranslationFile::create(['filename' => '_json']);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'Some string key. This one exists!',
        ]);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'Some string key. This one exists!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'Some string key',
        ])->assertSuccessful();

        $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'Some string key. This one exists! Not!',
        ])->assertSuccessful();
    }

    /** @test */
    public function translation_key_may_not_start_or_end_with_a_dot()
    {
        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'some-key.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => '.some-key',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('key');
    }

    /** @test */
    public function translation_key_may_start_or_end_with_a_dot_in_json_files()
    {
        $file = TranslationFile::create(['filename' => '_json']);

        $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => '.Some string key.',
        ])->assertSuccessful();
    }

    /** @test */
    public function translations_can_be_omitted()
    {
        $file = TranslationFile::create(['filename' => 'test-file']);

        $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'some.key',
        ])->assertSuccessful();

        $keys = $file->keys()->get();
        $this->assertCount(1, $keys);
        $this->assertEquals([], $keys->first()->translations);
    }

    /** @test */
    public function translations_should_be_an_array()
    {
        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'some.key',
            'translations' => 'not-an-array',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('translations');

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'some.key',
            'translations' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('translations');
    }

    /** @test */
    public function translation_values_should_be_a_string()
    {
        $file = TranslationFile::create(['filename' => 'test-file']);

        $response = $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'some.key',
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
        $file = TranslationFile::create(['filename' => 'test-file']);

        $this->actingAsUser()->postJson(route('translator.keys.store', [$file]), [
            'key' => 'some.key',
            'translations' => [
                'en' => null,
            ],
        ])->assertSuccessful();
    }
}
