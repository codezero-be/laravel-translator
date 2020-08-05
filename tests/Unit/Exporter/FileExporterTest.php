<?php

namespace CodeZero\Translator\Tests\Unit\Exporter;

use CodeZero\Translator\Exporter\FileExporter;
use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Tests\FileTestCase;
use Illuminate\Support\Facades\File;

class FileExporterTest extends FileTestCase
{
    /** @test */
    public function it_exports_translation_files()
    {
        $translationFiles = [
            $translationFileA = TranslationFile::make(['vendor' => null, 'filename' => 'test-file-a']),
            $translationFileB = TranslationFile::make(['vendor' => null, 'filename' => 'test-file-b']),
        ];

        $translationFileA->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key-a-1'])
                ->addTranslation('en', 'translation a-1 [en]')
                ->addTranslation('nl', 'translation a-1 [nl]'),
            TranslationKey::make(['key' => 'key-a-2'])
                ->addTranslation('en', 'translation a-2 [en]')
                ->addTranslation('nl', 'translation a-2 [nl]'),
        ]);

        $translationFileB->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'some.nested.key-b'])
                ->addTranslation('en', 'nested translation b [en]')
                ->addTranslation('nl', 'nested translation b [nl]'),
        ]);

        $exporter = new FileExporter();
        $exporter->export($translationFiles, $this->getLangPath());

        $file = $this->getLangPath('en/test-file-a.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-a-1' => 'translation a-1 [en]',
            'key-a-2' => 'translation a-2 [en]',
        ], include $file);

        $file = $this->getLangPath('nl/test-file-a.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-a-1' => 'translation a-1 [nl]',
            'key-a-2' => 'translation a-2 [nl]',
        ], include $file);

        $file = $this->getLangPath('en/test-file-b.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'some' => [
                'nested' => [
                    'key-b' => 'nested translation b [en]',
                ],
            ],
        ], include $file);

        $file = $this->getLangPath('nl/test-file-b.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'some' => [
                'nested' => [
                    'key-b' => 'nested translation b [nl]',
                ],
            ],
        ], include $file);
    }

    /** @test */
    public function it_exports_vendor_files()
    {
        $translationFiles = [
            $translationFile = TranslationFile::make(['vendor' => 'vendor-name', 'filename' => 'test-file']),
        ];

        $translationFile->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key'])
                ->addTranslation('en', 'translation [en]')
                ->addTranslation('nl', 'translation [nl]')
        ]);

        $exporter = new FileExporter();
        $exporter->export($translationFiles, $this->getLangPath());

        $file = $this->getLangPath('vendor/vendor-name/en/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => 'translation [en]',
        ], include $file);

        $file = $this->getLangPath('vendor/vendor-name/nl/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => 'translation [nl]',
        ], include $file);
    }

    /** @test */
    public function it_exports_json_files()
    {
        $translationFiles = [
            $translationFile = TranslationFile::make(['vendor' => null, 'filename' => '_json']),
        ];

        $translationFile->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'some.key'])
                ->addTranslation('en', 'translation [en]')
                ->addTranslation('nl', 'translation [nl]')
        ]);

        $exporter = new FileExporter();
        $exporter->export($translationFiles, $this->getLangPath());

        $file = $this->getLangPath('en.json');
        $this->assertFileExists($file);
        $this->assertEquals([
            'some.key' => 'translation [en]',
        ], json_decode(File::get($file), true));

        $file = $this->getLangPath('nl.json');
        $this->assertFileExists($file);
        $this->assertEquals([
            'some.key' => 'translation [nl]',
        ], json_decode(File::get($file), true));
    }

    /** @test */
    public function it_does_not_export_empty_translation_files_by_default()
    {
        $translationFiles = [
            $translationFile = TranslationFile::make(['vendor' => null, 'filename' => 'test-file']),
        ];

        $translationFile->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key-1'])
                ->addTranslation('en', ''),
        ]);

        $exporter = new FileExporter();
        $exporter->export($translationFiles, $this->getLangPath());

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileNotExists($file);
    }

    /** @test */
    public function it_does_not_export_missing_translations_by_default()
    {
        $translationFiles = [
            $translationFile = TranslationFile::make(['vendor' => null, 'filename' => 'test-file']),
        ];

        $translationFile->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key-1'])
                ->addTranslation('en', 'translation 1 [en]')
                ->addTranslation('nl', 'translation 1 [nl]')
                ->addTranslation('fr', 'translation 1 [fr]'),
            TranslationKey::make(['key' => 'key-2'])
                ->addTranslation('en', 'translation 2 [en]')
                ->addTranslation('nl', ''),
        ]);

        $exporter = new FileExporter();
        $exporter->export($translationFiles, $this->getLangPath());

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-1' => 'translation 1 [en]',
            'key-2' => 'translation 2 [en]',
        ], include $file);

        $file = $this->getLangPath('nl/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-1' => 'translation 1 [nl]',
        ], include $file);

        $file = $this->getLangPath('fr/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-1' => 'translation 1 [fr]',
        ], include $file);
    }

    /** @test */
    public function it_can_export_missing_translations()
    {
        $translationFiles = [
            $translationFileA = TranslationFile::make(['vendor' => null, 'filename' => 'test-file-a']),
            $translationFileB = TranslationFile::make(['vendor' => null, 'filename' => 'test-file-b']),
        ];

        $translationFileA->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key-a'])
                ->addTranslation('en', 'translation a [en]')
                ->addTranslation('nl', ''),
        ]);

        $translationFileB->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key-b'])
                ->addTranslation('fr', 'translation b [fr]')
                ->addTranslation('de', ''),
        ]);

        $exporter = new FileExporter();
        $exporter->includeMissing()->export($translationFiles, $this->getLangPath());

        $file = $this->getLangPath('en/test-file-a.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-a' => 'translation a [en]',
        ], include $file);

        $file = $this->getLangPath('nl/test-file-a.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-a' => '',
        ], include $file);

        $file = $this->getLangPath('fr/test-file-a.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-a' => '',
        ], include $file);

        $file = $this->getLangPath('de/test-file-a.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-a' => '',
        ], include $file);

        $file = $this->getLangPath('en/test-file-b.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-b' => '',
        ], include $file);

        $file = $this->getLangPath('nl/test-file-b.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-b' => '',
        ], include $file);

        $file = $this->getLangPath('fr/test-file-b.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-b' => 'translation b [fr]',
        ], include $file);

        $file = $this->getLangPath('de/test-file-b.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key-b' => '',
        ], include $file);
    }

    /** @test */
    public function it_can_export_specific_locales()
    {
        $translationFiles = [
            $translationFile = TranslationFile::make(['vendor' => null, 'filename' => 'test-file']),
        ];

        $translationFile->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key'])
                ->addTranslation('en', 'translation [en]')
                ->addTranslation('nl', 'translation [nl]')
                ->addTranslation('fr', 'translation [fr]'),
        ]);

        $exporter = new FileExporter();
        $exporter->onlyLocales(['en', 'nl'])->export($translationFiles, $this->getLangPath());

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

        $file = $this->getLangPath('fr/test-file.php');
        $this->assertFileNotExists($file);
    }

    /** @test */
    public function it_can_export_specific_locales_including_missing_translations()
    {
        $translationFiles = [
            $translationFile = TranslationFile::make(['vendor' => null, 'filename' => 'test-file']),
        ];

        $translationFile->setRelation('translationKeys', [
            TranslationKey::make(['key' => 'key'])
                ->addTranslation('en', 'translation [en]')
                ->addTranslation('nl', ''),
        ]);

        $exporter = new FileExporter();
        $exporter->onlyLocales(['en', 'nl', 'fr'])->includeMissing()->export($translationFiles, $this->getLangPath());

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

        $file = $this->getLangPath('fr/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals([
            'key' => '',
        ], include $file);
    }

    /** @test */
    public function it_cleans_the_destination_directory_before_exporting()
    {
        $translationFiles = [
            TranslationFile::make(['vendor' => null, 'filename' => 'test-file']),
        ];

        $this->createTranslationFile('not/exported.php', []);
        $this->assertFileExists($this->getLangPath('not/exported.php'));

        $exporter = new FileExporter();
        $exporter->export($translationFiles, $this->getLangPath());

        $this->assertFileNotExists($this->getLangPath('not/exported.php'));
    }

    /** @test */
    public function it_formats_the_exported_files_nicely()
    {
        $translationFiles = [
            $translationFilePHP = TranslationFile::make(['vendor' => null, 'filename' => 'test-file']),
            $translationFileJSON = TranslationFile::make(['vendor' => null, 'filename' => '_json']),
        ];

        $key = TranslationKey::make(['key' => 'key'])->addTranslation('en', 'translation [en]');

        $translationFilePHP->setRelation('translationKeys', [$key]);
        $translationFileJSON->setRelation('translationKeys', [$key]);

        $exporter = new FileExporter();
        $exporter->export($translationFiles, $this->getLangPath());

        $expectedPhpFormat = <<<'EOT'
<?php

return [
    'key' => 'translation [en]',
];
EOT;

        $expectedJsonFormat = <<<'EOT'
{
    "key": "translation [en]"
}
EOT;

        $file = $this->getLangPath('en/test-file.php');
        $this->assertFileExists($file);
        $this->assertEquals($expectedPhpFormat, File::get($file));

        $file = $this->getLangPath('en.json');
        $this->assertFileExists($file);
        $this->assertEquals($expectedJsonFormat, File::get($file));
    }
}
