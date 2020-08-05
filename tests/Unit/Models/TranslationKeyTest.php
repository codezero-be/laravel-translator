<?php

namespace CodeZero\Translator\Tests\Unit\Models;

use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Tests\TestCase;

class TranslationKeyTest extends TestCase
{
    /** @test */
    public function it_gets_all_translations()
    {
        $key = TranslationKey::make([
            'translations' => [
                'en' => 'translation [en]',
                'nl' => 'translation [nl]',
            ],
        ]);

        $this->assertEquals([
            'en' => 'translation [en]',
            'nl' => 'translation [nl]',
        ], $key->getTranslations());
    }

    /** @test */
    public function it_returns_an_empty_array_if_there_are_no_translations()
    {
        $key = TranslationKey::make();

        $this->assertEquals([], $key->getTranslations());
    }

    /** @test */
    public function it_gets_a_translation_in_a_specific_locale()
    {
        $key = TranslationKey::make([
            'translations' => [
                'en' => 'translation [en]',
                'nl' => 'translation [nl]',
            ],
        ]);

        $this->assertEquals('translation [en]', $key->getTranslation('en'));
        $this->assertEquals('translation [nl]', $key->getTranslation('nl'));
        $this->assertNull($key->getTranslation('fr'));

        $this->assertEquals('translation [en]', $key->en);
        $this->assertEquals('translation [nl]', $key->nl);
        $this->assertNull($key->fr);
    }

    /** @test */
    public function it_adds_translations()
    {
        $key = TranslationKey::make();
        $key->addTranslation('en', 'translation [en]');
        $key->addTranslation('nl', 'translation [nl]');

        $this->assertEquals([
            'en' => 'translation [en]',
            'nl' => 'translation [nl]',
        ], $key->translations);
    }

    /** @test */
    public function it_replaces_translations()
    {
        $key = TranslationKey::make();
        $key->addTranslation('en', 'translation A [en]');
        $key->addTranslation('nl', 'translation A [nl]');
        $key->addTranslation('en', 'translation B [en]');

        $this->assertEquals([
            'en' => 'translation B [en]',
            'nl' => 'translation A [nl]',
        ], $key->translations);
    }

    /** @test */
    public function it_stores_translations_as_json()
    {
        $key = TranslationKey::make();
        $key->addTranslation('en', 'translation [en]');
        $key->addTranslation('nl', 'translation [nl]');

        $this->assertEquals(json_encode([
            'en' => 'translation [en]',
            'nl' => 'translation [nl]',
        ]), $key->getAttributes()['translations']);
    }
}
