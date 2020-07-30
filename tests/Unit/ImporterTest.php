<?php

namespace CodeZero\Translator\Tests\Unit;

use CodeZero\Translator\Importer;
use CodeZero\Translator\FileLoader\LoadedFile;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImporterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_imports_translation_file_objects()
    {
        $loadedFiles = [
            (new LoadedFile('filename', 'vendor-name'))
                ->addTranslation('key-a', 'en', 'translation a [en]')
                ->addTranslation('key-a', 'nl', 'translation a [nl]')
                ->addTranslation('key-b', 'en', 'translation b [en]')
                ->addTranslation('key-b', 'nl', 'translation b [nl]')
        ];

        $importer = new Importer();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals('vendor-name', $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(2, $translationFile->translationKeys);

        $this->assertEquals('key-a', $translationFile->translationKeys[0]->key);
        $this->assertEquals([
            'en' => 'translation a [en]',
            'nl' => 'translation a [nl]',
        ], $translationFile->translationKeys[0]->translations);

        $this->assertEquals('key-b', $translationFile->translationKeys[1]->key);
        $this->assertEquals([
            'en' => 'translation b [en]',
            'nl' => 'translation b [nl]',
        ], $translationFile->translationKeys[1]->translations);
    }

    /** @test */
    public function it_imports_translation_file_arrays()
    {
        $loadedFiles = [
            [
                'vendor' => 'vendor-name',
                'filename' => 'filename',
                'translations' => [
                    'key-a' => [
                        'en' => 'translation a [en]',
                        'nl' => 'translation a [nl]',
                    ],
                    'key-b' => [
                        'en' => 'translation b [en]',
                        'nl' => 'translation b [nl]',
                    ],
                ],
            ],
        ];

        $importer = new Importer();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals('vendor-name', $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(2, $translationFile->translationKeys);

        $this->assertEquals('key-a', $translationFile->translationKeys[0]->key);
        $this->assertEquals([
            'en' => 'translation a [en]',
            'nl' => 'translation a [nl]',
        ], $translationFile->translationKeys[0]->translations);

        $this->assertEquals('key-b', $translationFile->translationKeys[1]->key);
        $this->assertEquals([
            'en' => 'translation b [en]',
            'nl' => 'translation b [nl]',
        ], $translationFile->translationKeys[1]->translations);
    }

    /** @test */
    public function vendor_is_optional()
    {
        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'translation [en]')
                ->addTranslation('key', 'nl', 'translation [nl]')
        ];

        $importer = new Importer();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->translationKeys);

        $this->assertEquals('key', $translationFile->translationKeys[0]->key);
        $this->assertEquals([
            'en' => 'translation [en]',
            'nl' => 'translation [nl]',
        ], $translationFile->translationKeys[0]->translations);
    }

    /** @test */
    public function it_does_not_replace_existing_translations_by_default()
    {
        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'filename',
        ]);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'new translation [en]')
                ->addTranslation('key', 'nl', 'new translation [nl]')
        ];

        $importer = new Importer();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->translationKeys);

        $this->assertEquals('key', $translationFile->translationKeys[0]->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => 'existing translation [nl]',
        ], $translationFile->translationKeys[0]->translations);
    }

    /** @test */
    public function it_can_replace_existing_translations()
    {
        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'filename',
        ]);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => 'existing translation [nl]',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'new translation [en]')
                ->addTranslation('key', 'nl', 'new translation [nl]')
        ];

        $importer = new Importer();
        $importer->replaceExisting()->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->translationKeys);

        $this->assertEquals('key', $translationFile->translationKeys[0]->key);
        $this->assertEquals([
            'en' => 'new translation [en]',
            'nl' => 'new translation [nl]',
        ], $translationFile->translationKeys[0]->translations);
    }

    /** @test */
    public function it_does_not_add_missing_translations_to_existing_keys_by_default()
    {
        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'filename',
        ]);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation [en]',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'new translation [en]')
                ->addTranslation('key', 'nl', 'new translation [nl]')
        ];

        $importer = new Importer();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->translationKeys);

        $this->assertEquals('key', $translationFile->translationKeys[0]->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
        ], $translationFile->translationKeys[0]->translations);
    }

    /** @test */
    public function it_can_add_missing_translations_to_existing_keys()
    {
        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'filename',
        ]);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation [en]',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'new translation [en]')
                ->addTranslation('key', 'nl', 'new translation [nl]')
        ];

        $importer = new Importer();
        $importer->addMissing()->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->translationKeys);

        $this->assertEquals('key', $translationFile->translationKeys[0]->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => 'new translation [nl]',
        ], $translationFile->translationKeys[0]->translations);
    }
}
