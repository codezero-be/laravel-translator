<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Tests\TestCase;

class ReadTranslationTest extends TestCase
{
    /** @test */
    public function it_lists_translations()
    {
        $firstTranslation = factory(Translation::class)->create(['key' => 'key.a']);
        $secondTranslation = factory(Translation::class)->create(['key' => 'key.b']);

        $this->get(route('translator.translations', $firstTranslation->file))
            ->assertStatus(200)
            ->assertSee('key.a')
            ->assertDontSee('key.b');

        $this->get(route('translator.translations', $secondTranslation->file))
            ->assertStatus(200)
            ->assertSee('key.b')
            ->assertDontSee('key.a');
    }

    /** @test */
    public function it_gets_translated_values()
    {
        $translation = factory(Translation::class)->make([
            'body' => [
                'nl' => 'Tekst',
                'en' => 'Text',
            ]
        ]);

        $this->assertEquals('Tekst', $translation->nl);
        $this->assertEquals('Text', $translation->en);
        $this->assertNull($translation->fr);

        app()->setLocale('nl');
        $this->assertEquals('Tekst', $translation->body);

        app()->setLocale('en');
        $this->assertEquals('Text', $translation->body);

        if (config('laravel-translatable.fallback_locale') === 'en') {
            app()->setLocale('fr');
            $this->assertEquals('Text', $translation->body);
        }
    }
}
