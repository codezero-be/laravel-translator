<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Routes\TranslatorRoutes;
use CodeZero\Translator\Tests\FileTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class ImportTranslationsTest extends FileTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_imports_translation_files()
    {
        $this->withoutExceptionHandling();

        $this->createTranslationFile('en/test-file.php', [
            'key-1' => 'translation 1',
            'key-2' => 'translation 2',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $this->actingAsUser()->post(route('translator.import'))->assertSuccessful();

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles->first();
        $this->assertEquals('test-file', $translationFile->filename);
        $this->assertCount(2, $translationFile->translationKeys);

        $translationKey = $translationFile->translationKeys[0];
        $this->assertEquals('key-1', $translationKey->key);
        $this->assertCount(1, $translationKey->translations);
        $this->assertEquals('translation 1', $translationKey->getTranslation('en'));

        $translationKey = $translationFile->translationKeys[1];
        $this->assertEquals('key-2', $translationKey->key);
        $this->assertCount(1, $translationKey->translations);
        $this->assertEquals('translation 2', $translationKey->getTranslation('en'));
    }

    /** @test */
    public function it_returns_all_translation_files_in_the_database()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'existing-file']);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'existing-key',
            'translations' => [
                'en' => 'existing translation',
            ],
        ]);

        $this->createTranslationFile('en/new-file.php', [
            'new-key' => 'new translation',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $response = $this->actingAsUser()->post(route('translator.import'));
        $response->assertSuccessful();

        $translationFiles = $response->original;
        $this->assertCount(2, $translationFiles);
        $this->assertEquals('existing-file', $translationFiles->first()->filename);
        $this->assertEquals('new-file', $translationFiles->last()->filename);
    }

    /** @test */
    public function it_does_not_add_missing_translations_to_existing_keys_by_default()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation [en]',
            ],
        ]);

        $this->createTranslationFile('nl/test-file.php', [
            'key' => 'new translation [nl]',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $response = $this->actingAsUser()->post(route('translator.import'));
        $response->assertSuccessful();

        $translationFiles = $response->original;
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles->first();
        $this->assertCount(1, $translationFile->translationKeys);
        $this->assertCount(1, $translationFile->translationKeys[0]->translations);
        $this->assertEquals('existing translation [en]', $translationFile->translationKeys[0]->getTranslation('en'));
    }

    /** @test */
    public function it_can_add_missing_translations_to_existing_keys()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation [en]',
                'nl' => '',
            ],
        ]);

        $this->createTranslationFile('nl/test-file.php', [
            'key' => 'new translation [nl]',
        ]);

        $this->createTranslationFile('fr/test-file.php', [
            'key' => 'new translation [fr]',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $response = $this->actingAsUser()->post(route('translator.import'), [
            'fill_missing' => true,
        ]);
        $response->assertSuccessful();

        $translationFiles = $response->original;
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles->first();
        $this->assertCount(1, $translationFile->translationKeys);
        $this->assertCount(3, $translationFile->translationKeys[0]->translations);
        $this->assertEquals('existing translation [en]', $translationFile->translationKeys[0]->getTranslation('en'));
        $this->assertEquals('new translation [nl]', $translationFile->translationKeys[0]->getTranslation('nl'));
        $this->assertEquals('new translation [fr]', $translationFile->translationKeys[0]->getTranslation('fr'));
    }

    /** @test */
    public function it_does_not_replace_existing_translations_by_default()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation',
            ],
        ]);

        $this->createTranslationFile('en/test-file.php', [
            'key' => 'new translation',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $response = $this->actingAsUser()->post(route('translator.import'));
        $response->assertSuccessful();

        $translationFiles = $response->original;
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles->first();
        $this->assertCount(1, $translationFile->translationKeys);
        $this->assertCount(1, $translationFile->translationKeys[0]->translations);
        $this->assertEquals('existing translation', $translationFile->translationKeys[0]->getTranslation('en'));
    }

    /** @test */
    public function it_can_replace_existing_translations()
    {
        $this->withoutExceptionHandling();

        $file = TranslationFile::create(['filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $file->id,
            'key' => 'key',
            'translations' => [
                'en' => 'existing translation',
            ],
        ]);

        $this->createTranslationFile('en/test-file.php', [
            'key' => 'new translation',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $response = $this->actingAsUser()->post(route('translator.import'), [
            'replace_existing' => true,
        ]);
        $response->assertSuccessful();

        $translationFiles = $response->original;
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles->first();
        $this->assertCount(1, $translationFile->translationKeys);
        $this->assertCount(1, $translationFile->translationKeys[0]->translations);
        $this->assertEquals('new translation', $translationFile->translationKeys[0]->getTranslation('en'));
    }

    /** @test */
    public function it_does_not_import_empty_translations_by_default()
    {
        $this->withoutExceptionHandling();

        $this->createTranslationFile('en/test-file.php', [
            'key' => '',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $response = $this->actingAsUser()->post(route('translator.import'));
        $response->assertSuccessful();

        $translationFiles = $response->original;
        $this->assertCount(0, $translationFiles);
    }

    /** @test */
    public function it_can_import_empty_translations()
    {
        $this->withoutExceptionHandling();

        $this->createTranslationFile('en/test-file.php', [
            'key' => '',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        $response = $this->actingAsUser()->post(route('translator.import'), [
            'include_empty' => true,
        ]);
        $response->assertSuccessful();

        $translationFiles = $response->original;
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles->first();
        $this->assertCount(1, $translationFile->translationKeys);
        $this->assertCount(1, $translationFile->translationKeys[0]->translations);
        $this->assertEquals('', $translationFile->translationKeys[0]->getTranslation('en'));
    }

    /** @test */
    public function it_can_import_specific_locales()
    {
        $this->withoutExceptionHandling();

        $this->createTranslationFile('en/test-file.php', [
            'key' => 'translation [en]',
        ]);

        $this->createTranslationFile('nl/test-file.php', [
            'key' => 'translation [nl]',
        ]);

        $this->createTranslationFile('fr/test-file.php', [
            'key' => 'translation [fr]',
        ]);

        Config::set('translator.import.path', $this->getLangPath());

        TranslatorRoutes::register();

        Config::set('translator.locales', ['en', 'nl']);

        $this->actingAsUser()->post(route('translator.import'))->assertSuccessful();

        $translationFiles = TranslationFile::all();
        $this->assertCount(1, $translationFiles);

        $translationFile = $translationFiles->first();
        $this->assertCount(1, $translationFile->translationKeys);
        $this->assertCount(2, $translationFile->translationKeys[0]->translations);
        $this->assertEquals('translation [en]', $translationFile->translationKeys[0]->getTranslation('en'));
        $this->assertEquals('translation [nl]', $translationFile->translationKeys[0]->getTranslation('nl'));
    }
}
