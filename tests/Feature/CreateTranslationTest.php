<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\Concerns\ChecksForValidationErrors;
use CodeZero\Translator\Tests\TestCase;

class CreateTranslationTest extends TestCase
{
    use ChecksForValidationErrors;

    /** @test */
    public function it_adds_a_translation()
    {
        $file = factory(TranslationFile::class)->create();
        $body = [
            'nl' => 'Tekst',
            'en' => 'Text',
        ];

        $this->assertCount(0, $file->translations()->get());

        $this->postJson(route('translator.translations', $file), [
            'is_html' => true,
            'key' => 'my.key',
            'body' => $body,
        ])->assertStatus(200);

        $translations = $file->translations()->get();

        $this->assertCount(1, $translations);
        $this->assertTrue($translations->first()->is_html);
        $this->assertEquals('my.key', $translations->first()->key);
        $this->assertEquals($body, $translations->first()->getTranslations('body'));
    }

    /** @test */
    public function is_html_is_false_by_default()
    {
        $file = factory(TranslationFile::class)->create();

        $this->postJson(route('translator.translations', $file), [
            'key' => 'my.key',
        ])->assertStatus(200);

        $translations = $file->translations()->get();

        $this->assertCount(1, $translations);
        $this->assertFalse($translations->first()->is_html);
    }

    /** @test */
    public function is_html_should_be_a_boolean()
    {
        $file = factory(TranslationFile::class)->create();

        $response = $this->postJson(route('translator.translations', $file), [
            'is_html' => 'invalid',
            'key' => 'my.key',
        ]);

        $this->assertValidationError($response, 'is_html');
    }

    /** @test */
    public function translation_key_is_required()
    {
        $file = factory(TranslationFile::class)->create();

        $response = $this->postJson(route('translator.translations.store', $file), [
            'key' => null,
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_key_should_be_unique_within_the_same_file()
    {
        $fileOne = factory(TranslationFile::class)->create();
        $fileTwo = factory(TranslationFile::class)->create();

        factory(Translation::class)->create([
            'file_id' => $fileOne->id,
            'key' => 'existing.key',
        ]);

        $response = $this->postJson(route('translator.translations.store', $fileOne), [
            'key' => 'existing.key',
        ]);

        $this->assertValidationError($response, 'key');

        $response = $this->postJson(route('translator.translations.store', $fileTwo), [
            'key' => 'existing.key',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function translation_key_should_not_already_be_a_namespace()
    {
        $existingTranslation = factory(Translation::class)->create(['key' => 'some.namespace.key']);

        $response = $this->postJson(route('translator.translations.store', $existingTranslation->file), [
            'key' => 'some.namespace',
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_namespace_should_not_already_be_a_key()
    {
        $existingTranslation = factory(Translation::class)->create(['key' => 'some.namespace']);

        $response = $this->postJson(route('translator.translations.store', $existingTranslation->file), [
            'key' => 'some.namespace.key',
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_key_must_contain_only_letters_numbers_dashes_and_dots()
    {
        $file = factory(TranslationFile::class)->create();

        $response = $this->postJson(route('translator.translations.store', $file), [
            'key' => 'this.is.my-1st-key',
        ]);

        $response->assertStatus(200);

        $response = $this->postJson(route('translator.translations.store', $file), [
            'key' => 'this.is.my_1st_key',
        ]);

        $this->assertValidationError($response, 'key');

        $response = $this->postJson(route('translator.translations.store', $file), [
            'key' => "th'",
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_key_may_not_start_or_end_with_a_dot()
    {
        $file = factory(TranslationFile::class)->create();

        $response = $this->postJson(route('translator.translations.store', $file), [
            'key' => '.my-key',
        ]);

        $this->assertValidationError($response, 'key');

        $response = $this->postJson(route('translator.translations.store', $file), [
            'key' => 'my-key.',
        ]);

        $this->assertValidationError($response, 'key');
    }

    /** @test */
    public function translation_keys_are_converted_to_lower_case()
    {
        $file = factory(TranslationFile::class)->create();

        $response = $this->postJson(route('translator.translations.store', $file), [
            'key' => 'This.Is.My-1st-Key',
        ]);

        $response->assertStatus(200);

        $this->assertEquals(
            'this.is.my-1st-key',
            $file->translations()->first()->key
        );
    }

    /** @test */
    public function translated_values_are_optional()
    {
        $file = factory(TranslationFile::class)->create();

        $this->postJson(route('translator.translations.store', $file), [
            'key' => 'my.key',
            'body' => null,
        ])->assertStatus(200);

        $translations = $file->translations()->get();

        $this->assertCount(1, $translations);
        $this->assertEquals('', $translations->first()->body);
        $this->assertNull($translations->first()->nl);
    }

    /** @test */
    public function translation_value_may_contain_html()
    {
        $contents = file_get_contents( __DIR__.'/../_lang-test-files/lang-test-html/en/sample.html');

        $file = factory(TranslationFile::class)->create();

        $this->postJson(route('translator.translations.store', $file), [
            'key' => 'my.key',
            'body' => [
                'en' => $contents,
            ],
        ])->assertStatus(200);

        $translations = $file->translations()->get();

        $this->assertCount(1, $translations);
        $this->assertEquals(trim($contents), $translations->first()->en);
    }
}
