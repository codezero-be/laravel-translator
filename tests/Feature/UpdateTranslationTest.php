<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\Concerns\ChecksForValidationErrors;
use CodeZero\Translator\Tests\TestCase;

class UpdateTranslationTest extends TestCase
{
    use ChecksForValidationErrors;

    /** @test */
    public function it_updates_a_translation()
    {
        $translation = factory(Translation::class)->create([
            'is_html' => false,
            'key' => 'some.key',
            'body' => [
                'nl' => 'nl',
            ],
        ]);

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'is_html' => true,
            'key' => 'another.key',
            'body' => [
                'nl' => 'Tekst',
                'en' => 'Text',
            ],
        ]);

        $response->assertStatus(200);

        $translation = $translation->fresh();
        $this->assertTrue($translation->is_html);
        $this->assertEquals('another.key', $translation->key);
        $this->assertEquals([
            'nl' => 'Tekst',
            'en' => 'Text',
        ], $translation->getTranslations('body'));
    }

    /** @test */
    public function is_html_is_optional()
    {
        $translation = factory(Translation::class)->create([
            'is_html' => true,
        ]);

        $this->patchJson(route('translator.translations.update', $translation), [
            'key' => 'my.key',
        ])->assertStatus(200);

        $translation = $translation->fresh();
        $this->assertTrue($translation->is_html);
    }

    /** @test */
    public function is_html_should_be_a_boolean()
    {
        $translation = factory(Translation::class)->create([
            'is_html' => true,
        ]);

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'is_html' => 'invalid',
            'key' => 'my.key',
        ]);

        $this->assertValidationError($response, 'is_html');
    }

    /** @test */
    public function translation_key_is_required()
    {
        $translation = factory(Translation::class)->create([
            'key' => 'some.key',
        ]);

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'key' => null,
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_key_should_be_unique_within_the_same_file()
    {
        $file = factory(TranslationFile::class)->create();

        $currentTranslation = factory(Translation::class)->create([
            'file_id' => $file->id,
            'key' => 'current.key',
        ]);

        $otherTranslation = factory(Translation::class)->create([
            'file_id' => $file->id,
            'key' => 'other.key',
        ]);

        $response = $this->patchJson(route('translator.translations.update', $currentTranslation), [
            'key' => $otherTranslation->key,
        ]);

        $this->assertValidationError($response, 'key');

        $this->patchJson(route('translator.translations.update', $currentTranslation), [
            'key' => $currentTranslation->key,
        ])->assertStatus(200);

        $this->patchJson(route('translator.translations.update', $currentTranslation), [
            'key' => 'new.key',
        ])->assertStatus(200);
    }

    /** @test */
    public function translation_key_should_not_already_be_a_namespace()
    {
        $file = factory(TranslationFile::class)->create();

        $currentTranslation = factory(Translation::class)->create([
            'file_id' => $file->id,
            'key' => 'some.namespace.key',
        ]);

        factory(Translation::class)->create([
            'file_id' => $file->id,
            'key' => 'other.namespace.key',
        ]);

        $response = $this->patchJson(route('translator.translations.update', $currentTranslation), [
            'key' => 'other.namespace',
        ]);

        $this->assertValidationError($response, 'key');

        $response = $this->patchJson(route('translator.translations.update', $currentTranslation), [
            'key' => 'some.namespace',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function translation_namespace_should_not_already_be_a_key()
    {
        $file = factory(TranslationFile::class)->create();

        $currentTranslation = factory(Translation::class)->create([
            'file_id' => $file->id,
            'key' => 'some.namespace',
        ]);

        factory(Translation::class)->create([
            'file_id' => $file->id,
            'key' => 'other.namespace',
        ]);

        $response = $this->patchJson(route('translator.translations.update', $currentTranslation), [
            'key' => 'other.namespace.key',
        ]);

        $this->assertValidationError($response, 'key');

        $response = $this->patchJson(route('translator.translations.update', $currentTranslation), [
            'key' => 'some.namespace.key',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function translation_key_must_contain_only_letters_numbers_dashes_and_dots()
    {
        $translation = factory(Translation::class)->create([
            'key' => 'some.key',
        ]);

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'key' => 'some_key',
        ]);

        $this->assertValidationError($response, 'key');

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'key' => "th'",
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_key_may_not_start_or_end_with_a_dot()
    {
        $translation = factory(Translation::class)->create([
            'key' => 'my-key',
        ]);

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'key' => '.my-key',
        ]);

        $this->assertValidationError($response, 'key');

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'key' => 'my-key.',
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_keys_are_converted_to_lower_case()
    {
        $translation = factory(Translation::class)->create();

        $this->patchJson(route('translator.translations.update', $translation), [
            'key' => 'Some.Key',
        ])->assertStatus(200);

        $this->assertEquals('some.key', $translation->fresh()->key);
    }

    /** @test */
    public function body_can_be_updated_separately()
    {
        $translation = factory(Translation::class)->create([
            'key' => 'some.key',
            'body' => [
                'nl' => 'nl',
            ],
        ]);

        $response = $this->patchJson(route('translator.translations.update', $translation), [
            'body' => [
                'en' => 'Text',
            ],
        ]);

        $response->assertStatus(200);

        $translation = $translation->fresh();

        $this->assertEquals('some.key', $translation->key);
        $this->assertEquals([
            'en' => 'Text',
        ], $translation->getTranslations('body'));
    }

    /** @test */
    public function body_can_be_set_to_an_empty_array()
    {
        $translation = factory(Translation::class)->create([
            'key' => 'some.key',
            'body' => [
                'nl' => 'nl',
            ],
        ]);

        $this->patchJson(route('translator.translations.update', $translation), [
            'body' => [],
        ])->assertStatus(200);

        $this->assertEquals([], $translation->fresh()->getTranslations('body'));
    }

    /** @test */
    public function body_can_be_set_to_null()
    {
        $translation = factory(Translation::class)->create([
            'key' => 'some.key',
            'body' => [
                'nl' => 'nl',
            ],
        ]);

        $this->patchJson(route('translator.translations.update', $translation), [
            'body' => null,
        ])->assertStatus(200);

        $this->assertEquals([], $translation->fresh()->getTranslations('body'));
    }
}
