<?php

namespace CodeZero\Translator\Tests\Unit\FileLoader;

use CodeZero\Translator\FileLoader\LoadedFile;
use CodeZero\Translator\Tests\TestCase;

class LoadedFileTest extends TestCase
{
    /** @test */
    public function it_makes_a_new_translation_file_instance()
    {
        $translations = LoadedFile::make('fileName');
        $this->assertEquals('fileName', $translations->filename);
        $this->assertNull($translations->vendor);
        $this->assertEquals([], $translations->translations);

        $translations = LoadedFile::make('fileName', 'vendorName');
        $this->assertEquals('fileName', $translations->filename);
        $this->assertEquals('vendorName', $translations->vendor);
        $this->assertEquals([], $translations->translations);
    }

    /** @test */
    public function it_adds_translations()
    {
        $translations = LoadedFile::make('fileName');
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
        $translations = LoadedFile::make('fileName');
        $translations->addTranslation('key', 'en', 'translation A [en]');
        $translations->addTranslation('key', 'en', 'translation B [en]');

        $this->assertEquals([
            'key' => [
                'en' => 'translation B [en]',
            ]
        ], $translations->translations);
    }

    /** @test */
    public function it_converts_to_an_array()
    {
        $translations = LoadedFile::make('fileName', 'vendorName');
        $translations->addTranslation('key', 'en', 'translation [en]');
        $translations->addTranslation('key', 'nl', 'translation [nl]');

        $this->assertEquals([
            'vendor' => 'vendorName',
            'filename' => 'fileName',
            'translations' => [
                'key' => [
                    'en' => 'translation [en]',
                    'nl' => 'translation [nl]',
                ],
            ],
        ], $translations->toArray());
    }

    /** @test */
    public function it_converts_empty_translations_to_an_empty_array()
    {
        $translations = LoadedFile::make('fileName', 'vendorName');

        $this->assertEquals([
            'vendor' => 'vendorName',
            'filename' => 'fileName',
            'translations' => [],
        ], $translations->toArray());
    }

    /** @test */
    public function it_checks_if_it_has_translations()
    {
        $translations = LoadedFile::make('fileName');

        $this->assertFalse($translations->hasTranslations());

        $translations->addTranslation('key', 'en', 'translation [en]');

        $this->assertTrue($translations->hasTranslations());
    }
}
