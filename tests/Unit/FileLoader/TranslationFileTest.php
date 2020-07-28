<?php

namespace CodeZero\Translator\Tests\Unit\FileLoader;

use CodeZero\Translator\FileLoader\TranslationFile;
use CodeZero\Translator\Tests\TestCase;

class TranslationFileTest extends TestCase
{
    /** @test */
    public function it_makes_a_new_translation_file_instance()
    {
        $translations = new TranslationFile('fileName');
        $this->assertEquals('fileName', $translations->file);
        $this->assertNull($translations->vendor);
        $this->assertNull($translations->translations);

        $translations = new TranslationFile('fileName', 'vendorName');
        $this->assertEquals('fileName', $translations->file);
        $this->assertEquals('vendorName', $translations->vendor);
        $this->assertNull($translations->translations);
    }

    /** @test */
    public function it_adds_translations()
    {
        $translations = new TranslationFile('fileName');
        $translations->addTranslation('key', 'en', 'translation [en]');
        $translations->addTranslation('key', 'nl', 'translation [nl]');

        $this->assertEquals([
            'key' => [
                'en' => 'translation [en]',
                'nl' => 'translation [nl]',
            ]
        ], $translations->translations);
    }

    /** @test */
    public function it_replaces_translations()
    {
        $translations = new TranslationFile('fileName');
        $translations->addTranslation('key', 'en', 'translation A [en]');
        $translations->addTranslation('key', 'en', 'translation B [en]');

        $this->assertEquals([
            'key' => [
                'en' => 'translation B [en]',
            ]
        ], $translations->translations);
    }
}
