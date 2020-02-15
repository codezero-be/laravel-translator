<?php

namespace CodeZero\Translator\Tests\Unit;

use CodeZero\Translator\Importer;
use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Tests\TestCase;

class ImporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->importer = new Importer();
    }

    protected function getLangPath($directory)
    {
        return __DIR__.'/../_lang-test-files/'.$directory;
    }

    /** @test */
    public function it_imports_translations()
    {
        $this->importer->import($this->getLangPath('lang-test-basic'));

        $translationFiles = TranslationFile::all();

        $this->assertCount(1, $translationFiles);

        $file = $translationFiles[0];

        $this->assertEquals('app', $file->name);
        $this->assertNull($file->package);
        $this->assertCount(1, $file->translations);

        $translation = $file->translations[0];

        $this->assertEquals('app-test', $translation->key);
        $this->assertEquals('App Test EN', $translation->en);
        $this->assertEquals('App Test NL', $translation->nl);
    }

    /** @test */
    public function it_imports_nested_array_keys()
    {
        $this->importer->import($this->getLangPath('lang-test-nested'));

        $translationFiles = TranslationFile::all();

        $this->assertCount(1, $translationFiles);

        $file = $translationFiles[0];

        $this->assertEquals('app', $file->name);
        $this->assertNull($file->package);
        $this->assertCount(1, $file->translations);

        $translation = $file->translations[0];

        $this->assertEquals('nested.app.test', $translation->key);
        $this->assertEquals('App Test EN', $translation->en);
        $this->assertEquals('App Test NL', $translation->nl);
    }

    /** @test */
    public function it_imports_package_translations()
    {
        $this->importer->import($this->getLangPath('lang-test-package'));

        $translationFiles = TranslationFile::all();

        $this->assertCount(2, $translationFiles);

        $file = $translationFiles[0];

        $this->assertEquals('app', $file->name);
        $this->assertNull($file->package);
        $this->assertCount(1, $file->translations);

        $translation = $file->translations[0];

        $this->assertEquals('app-test', $translation->key);
        $this->assertEquals('App Test EN', $translation->en);
        $this->assertEquals('App Test NL', $translation->nl);

        $file = $translationFiles[1];

        $this->assertEquals('feature', $file->name);
        $this->assertEquals('some-package', $file->package);
        $this->assertCount(2, $file->translations);

        $translation = $file->translations[0];

        $this->assertEquals('package-test-a', $translation->key);
        $this->assertEquals('Package Test A EN', $translation->en);
        $this->assertNull($translation->nl);

        $translation = $file->translations[1];

        $this->assertEquals('package-test-b', $translation->key);
        $this->assertEquals('Package Test B EN', $translation->en);
        $this->assertNull($translation->nl);
    }

    /** @test */
    public function it_imports_html_content()
    {
        $contents = file_get_contents($this->getLangPath('lang-test-html/en/sample.html'));

        $this->importer->import($this->getLangPath('lang-test-html'));

        $this->assertEquals($contents, Translation::first()->en);
    }

    /** @test */
    public function it_cleans_up_the_database_before_import()
    {
        factory(Translation::class, 5)->create();

        $this->assertCount(5, TranslationFile::all());
        $this->assertCount(5, Translation::all());

        $this->importer->import($this->getLangPath('lang-test-basic'));

        $translationFiles = TranslationFile::all();

        $this->assertCount(1, $translationFiles);

        $file = $translationFiles[0];

        $this->assertEquals('app', $file->name);
        $this->assertNull($file->package);
        $this->assertCount(1, $file->translations);

        $translation = $file->translations[0];

        $this->assertEquals('app-test', $translation->key);
        $this->assertEquals('App Test EN', $translation->en);
        $this->assertEquals('App Test NL', $translation->nl);
    }

    /** @test */
    public function it_syncs_imported_translations_with_existing_ones()
    {
        $translationFile = factory(TranslationFile::class)->create(['name' => 'app']);
        $translation = factory(Translation::class)->create([
            'file_id' => $translationFile->id,
            'key' => 'app-test',
            'body' => [
                'fr' => 'App Test FR',
            ],
        ]);

        $this->assertNull($translation->en);
        $this->assertNull($translation->nl);
        $this->assertEquals('App Test FR', $translation->fr);

        $this->importer->sync($this->getLangPath('lang-test-basic'));

        $translationFiles = TranslationFile::all();

        $this->assertCount(1, $translationFiles);
        $this->assertEquals('app', $translationFiles->first()->name);
        $this->assertCount(1, $translationFiles->first()->translations);

        $translation = $translationFiles->first()->translations->first();

        $this->assertEquals('app-test', $translation->key);
        $this->assertEquals('App Test EN', $translation->en);
        $this->assertEquals('App Test NL', $translation->nl);
        $this->assertEquals('App Test FR', $translation->fr);
    }

    /** @test */
    public function language_files_win_in_case_of_conflicts()
    {
        $translationFile = factory(TranslationFile::class)->create(['name' => 'app']);
        $translation = factory(Translation::class)->create([
            'file_id' => $translationFile->id,
            'key' => 'app-test',
            'body' => [
                'en' => 'Some Value',
            ],
        ]);

        $this->assertEquals('Some Value', $translation->en);

        $this->importer->sync($this->getLangPath('lang-test-basic'));

        $this->assertEquals('App Test EN', $translation->fresh()->en);
    }

    /** @test */
    public function database_translations_can_be_preferred_in_case_of_conflicts()
    {
        $translationFile = factory(TranslationFile::class)->create(['name' => 'app']);
        $translation = factory(Translation::class)->create([
            'file_id' => $translationFile->id,
            'key' => 'app-test',
            'body' => [
                'en' => 'Some Value',
            ],
        ]);

        $this->assertEquals('Some Value', $translation->en);

        $this->importer->databaseWins()->sync($this->getLangPath('lang-test-basic'));

        $this->assertEquals('Some Value', $translation->fresh()->en);
    }
}
