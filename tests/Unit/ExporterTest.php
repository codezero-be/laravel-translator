<?php

namespace CodeZero\Translator\Tests\Unit;

use CodeZero\Translator\Exporter;
use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Models\TranslationFile;
use File;
use CodeZero\Translator\Tests\TestCase;

class ExporterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->exporter = new Exporter();
        $this->destination = __DIR__.'/../_lang-test-files/lang-temp';

        File::deleteDirectory($this->destination);
        File::makeDirectory($this->destination);
    }

    protected function tearDown()
    {
        File::deleteDirectory($this->destination);
    }

    /** @test */
    public function it_exports_database_translations_to_language_files()
    {
        $appFile = factory(TranslationFile::class)->create([
            'name' => 'app',
            'package' => null,
        ]);

        factory(Translation::class)->create([
            'file_id' => $appFile->id,
            'key' => 'app-test-1',
            'body' => [
                'en' => 'App Test 1 EN',
                'nl' => 'App Test 1 NL',
            ],
        ]);

        factory(Translation::class)->create([
            'file_id' => $appFile->id,
            'key' => 'app-test-2',
            'body' => [
                'en' => 'App Test 2 EN',
            ],
        ]);

        $formsFileFile = factory(TranslationFile::class)->create([
            'name' => 'forms',
            'package' => null,
        ]);

        factory(Translation::class)->create([
            'file_id' => $formsFileFile->id,
            'key' => 'forms-test',
            'body' => [
                'en' => 'Forms Test EN',
            ],
        ]);

        $this->exporter->export($this->destination);

        $this->assertTrue(File::isFile($this->destination.'/en/app.php'));
        $this->assertTrue(File::isFile($this->destination.'/nl/app.php'));
        $this->assertTrue(File::isFile($this->destination.'/en/forms.php'));
        $this->assertFalse(File::isFile($this->destination.'/nl/forms.php'));

        $appEN = include $this->destination.'/en/app.php';
        $appNL = include $this->destination.'/nl/app.php';
        $formsEN = include $this->destination.'/en/forms.php';

        $this->assertEquals([
            'app-test-1' => 'App Test 1 EN',
            'app-test-2' => 'App Test 2 EN',
        ], $appEN);

        $this->assertEquals([
            'app-test-1' => 'App Test 1 NL',
        ], $appNL);

        $this->assertEquals([
            'forms-test' => 'Forms Test EN',
        ], $formsEN);
    }

    /** @test */
    public function it_exports_nested_array_keys()
    {
        $appFile = factory(TranslationFile::class)->create([
            'name' => 'nested',
            'package' => null,
        ]);

        factory(Translation::class)->create([
            'file_id' => $appFile->id,
            'key' => 'this.is.nested',
            'body' => [
                'en' => 'Nested Test',
            ],
        ]);

        factory(Translation::class)->create([
            'file_id' => $appFile->id,
            'key' => 'this.is.also-nested',
            'body' => [
                'en' => 'Also Nested Test',
            ],
        ]);

        $this->exporter->export($this->destination);

        $this->assertTrue(File::isFile($this->destination.'/en/nested.php'));

        $en = include $this->destination.'/en/nested.php';

        $this->assertEquals([
            'this' => [
                'is' => [
                    'nested' => 'Nested Test',
                    'also-nested' => 'Also Nested Test',
                ]
            ]
        ], $en);
    }

    /** @test */
    public function it_exports_package_translations()
    {
        $translationFile1 = factory(TranslationFile::class)->create([
            'name' => 'feature',
            'package' => 'package-1',
        ]);

        factory(Translation::class)->create([
            'file_id' => $translationFile1->id,
            'key' => 'package-test',
            'body' => [
                'en' => 'Package Test EN',
                'nl' => 'Package Test NL',
            ],
        ]);

        $translationFile2 = factory(TranslationFile::class)->create([
            'name' => 'nested',
            'package' => 'package-2',
        ]);

        factory(Translation::class)->create([
            'file_id' => $translationFile2->id,
            'key' => 'nested.package-test',
            'body' => [
                'en' => 'Nested Package Test EN',
            ],
        ]);

        $this->exporter->export($this->destination);

        $this->assertTrue(File::isFile($this->destination.'/vendor/package-1/en/feature.php'));
        $this->assertTrue(File::isFile($this->destination.'/vendor/package-1/nl/feature.php'));
        $this->assertTrue(File::isFile($this->destination.'/vendor/package-2/en/nested.php'));

        $notNestedEN = include $this->destination.'/vendor/package-1/en/feature.php';
        $notNestedNL = include $this->destination.'/vendor/package-1/nl/feature.php';
        $nestedEN = include $this->destination.'/vendor/package-2/en/nested.php';

        $this->assertEquals([
            'package-test' => 'Package Test EN',
        ], $notNestedEN);

        $this->assertEquals([
            'package-test' => 'Package Test NL',
        ], $notNestedNL);

        $this->assertEquals([
            'nested' => [
                'package-test' => 'Nested Package Test EN',
            ]
        ], $nestedEN);
    }

    /** @test */
    public function it_cleans_up_the_destination_folder_before_export()
    {
        File::put("{$this->destination}/trash.php", 'This should be deleted before export.');

        $this->exporter->export($this->destination);

        $this->assertFalse(File::isFile($this->destination.'/trash.php'));
    }
}
