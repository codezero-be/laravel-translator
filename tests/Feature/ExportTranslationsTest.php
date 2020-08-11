<?php

namespace CodeZero\Translator\Tests\Feature;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Tests\FileTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class ExportTranslationsTest extends FileTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_exports_translation_files()
    {
        $this->withoutExceptionHandling();

        $translationFile = TranslationFile::create(['vendor' => null, 'filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $translationFile->id,
            'key' => 'key',
            'translations' => [
                'en' => 'translation [en]',
                'nl' => 'translation [nl]',
            ],
        ]);

        Config::set('translator.export.path', $this->getLangPath());

        $this->actingAsUser()->post(route('translator.export'))->assertSuccessful();

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => 'translation [en]',
        ], include $file);

        $file = $this->getLangPath('nl/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => 'translation [nl]',
        ], include $file);
    }

    /** @test */
    public function it_does_not_export_empty_translations_by_default()
    {
        $this->withoutExceptionHandling();

        $translationFile = TranslationFile::create(['vendor' => null, 'filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $translationFile->id,
            'key' => 'key',
            'translations' => [
                'en' => '',
            ],
        ]);

        Config::set('translator.export.path', $this->getLangPath());

        $this->actingAsUser()->post(route('translator.export'))->assertSuccessful();

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileNotExists($file);
    }

    /** @test */
    public function it_can_export_empty_translations()
    {
        $this->withoutExceptionHandling();

        $translationFile = TranslationFile::create(['vendor' => null, 'filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $translationFile->id,
            'key' => 'key',
            'translations' => [
                'en' => '',
            ],
        ]);

        Config::set('translator.export.path', $this->getLangPath());

        $this->actingAsUser()->post(route('translator.export', [
            'include_empty' => true,
        ]))->assertSuccessful();

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => '',
        ], include $file);
    }

    /** @test */
    public function it_can_export_specific_locales()
    {
        $this->withoutExceptionHandling();

        $translationFile = TranslationFile::create(['vendor' => null, 'filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $translationFile->id,
            'key' => 'key',
            'translations' => [
                'en' => 'translation [en]',
                'nl' => 'translation [nl]',
                'fr' => 'translation [fr]',
            ],
        ]);

        Config::set('translator.export.path', $this->getLangPath());

        Config::set('translator.locales', ['en', 'nl']);

        $this->actingAsUser()->post(route('translator.export'))->assertSuccessful();

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => 'translation [en]',
        ], include $file);

        $file = $this->getLangPath('nl/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => 'translation [nl]',
        ], include $file);
    }

    /** @test */
    public function it_can_export_specific_locales_including_empty_translations()
    {
        $this->withoutExceptionHandling();

        $translationFile = TranslationFile::create(['vendor' => null, 'filename' => 'test-file']);

        TranslationKey::create([
            'file_id' => $translationFile->id,
            'key' => 'key',
            'translations' => [
                'en' => 'translation [en]',
                'nl' => '',
                'fr' => 'translation [fr]',
            ],
        ]);

        Config::set('translator.export.path', $this->getLangPath());

        Config::set('translator.locales', ['en', 'nl']);

        $this->actingAsUser()->post(route('translator.export'), [
            'include_empty' => true,
        ])->assertSuccessful();

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => 'translation [en]',
        ], include $file);

        $file = $this->getLangPath('nl/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => '',
        ], include $file);
    }
}
