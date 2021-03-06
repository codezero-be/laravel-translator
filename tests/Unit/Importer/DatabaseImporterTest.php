<?php

namespace CodeZero\Translator\Tests\Unit\Importer;

use CodeZero\Translator\Importer\DatabaseImporter;
use CodeZero\Translator\FileLoader\LoadedFile;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseImporterTest extends TestCase
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

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals('vendor-name', $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(2, $translationFile->keys);

        $this->assertEquals('key-a', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'translation a [en]',
            'nl' => 'translation a [nl]',
        ], $translationFile->keys[0]->translations);

        $this->assertEquals('key-b', $translationFile->keys[1]->key);
        $this->assertEquals([
            'en' => 'translation b [en]',
            'nl' => 'translation b [nl]',
        ], $translationFile->keys[1]->translations);
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

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals('vendor-name', $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(2, $translationFile->keys);

        $this->assertEquals('key-a', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'translation a [en]',
            'nl' => 'translation a [nl]',
        ], $translationFile->keys[0]->translations);

        $this->assertEquals('key-b', $translationFile->keys[1]->key);
        $this->assertEquals([
            'en' => 'translation b [en]',
            'nl' => 'translation b [nl]',
        ], $translationFile->keys[1]->translations);
    }

    /** @test */
    public function vendor_is_optional()
    {
        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'translation [en]')
                ->addTranslation('key', 'nl', 'translation [nl]')
        ];

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'translation [en]',
            'nl' => 'translation [nl]',
        ], $translationFile->keys[0]->translations);
    }

    /** @test */
    public function it_adds_new_keys_to_existing_files()
    {
        $file = TranslationFile::create([
            'vendor' => null,
            'filename' => 'filename',
        ]);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation [en]',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('new-key', 'en', 'new translation [en]')
        ];

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(2, $translationFile->keys);

        $this->assertEquals('existing-key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
        ], $translationFile->keys[0]->translations);

        $this->assertEquals('new-key', $translationFile->keys[1]->key);
        $this->assertEquals([
            'en' => 'new translation [en]',
        ], $translationFile->keys[1]->translations);
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
                'nl' => '',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'new translation [en]')
                ->addTranslation('key', 'nl', 'new translation [nl]')
                ->addTranslation('key', 'fr', 'new translation [fr]')
        ];

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => '',
        ], $translationFile->keys[0]->translations);
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
                'nl' => '',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'new translation [en]')
                ->addTranslation('key', 'nl', 'new translation [nl]')
                ->addTranslation('key', 'fr', 'new translation [fr]')
        ];

        $importer = new DatabaseImporter();
        $importer->fillMissing()->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => 'new translation [nl]',
            'fr' => 'new translation [fr]',
        ], $translationFile->keys[0]->translations);
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

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'existing translation [en]',
            'nl' => 'existing translation [nl]',
        ], $translationFile->keys[0]->translations);
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
                'nl' => '',
            ],
        ]);

        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'new translation [en]')
                ->addTranslation('key', 'nl', 'new translation [nl]')
        ];

        $importer = new DatabaseImporter();
        $importer->replaceExisting()->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'new translation [en]',
            'nl' => '',
        ], $translationFile->keys[0]->translations);
    }

    /** @test */
    public function it_does_not_import_empty_translations_by_default()
    {
        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'translation [en]')
                ->addTranslation('key', 'nl', '')
        ];

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'translation [en]',
        ], $translationFile->keys[0]->translations);
    }

    /** @test */
    public function it_does_not_import_empty_translation_files_and_keys_by_default()
    {
        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key-with-translation', 'en', 'translation [en]')
                ->addTranslation('empty-key', 'nl', ''),
            (new LoadedFile('filename-without-keys'))
        ];

        $importer = new DatabaseImporter();
        $importer->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key-with-translation', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'translation [en]',
        ], $translationFile->keys[0]->translations);
    }

    /** @test */
    public function it_can_import_empty_translations()
    {
        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'translation [en]')
                ->addTranslation('key', 'nl', '')
        ];

        $importer = new DatabaseImporter();
        $importer->includeEmpty()->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'translation [en]',
            'nl' => '',
        ], $translationFile->keys[0]->translations);
    }

    /** @test */
    public function it_can_import_specific_locales()
    {
        $loadedFiles = [
            (new LoadedFile('filename'))
                ->addTranslation('key', 'en', 'translation [en]')
                ->addTranslation('key', 'nl', 'translation [nl]')
                ->addTranslation('key', 'fr', 'translation [fr]')
        ];

        $importer = new DatabaseImporter();
        $importer->onlyLocales(['en', 'nl', 'de'])->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'en' => 'translation [en]',
            'nl' => 'translation [nl]',
        ], $translationFile->keys[0]->translations);
    }

    /** @test */
    public function it_can_purge_the_database_before_import()
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
                ->addTranslation('key', 'nl', 'new translation [nl]')
        ];

        $importer = new DatabaseImporter();
        $importer->purgeDatabase()->import($loadedFiles);

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles[0];
        $this->assertEquals(null, $translationFile->vendor);
        $this->assertEquals('filename', $translationFile->filename);
        $this->assertCount(1, $translationFile->keys);

        $this->assertEquals('key', $translationFile->keys[0]->key);
        $this->assertEquals([
            'nl' => 'new translation [nl]',
        ], $translationFile->keys[0]->translations);
    }
}
