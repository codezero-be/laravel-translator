<?php

namespace CodeZero\Translator\Tests\Unit\FileLoader;

use CodeZero\Translator\FileLoader\LaravelFileLoader;
use CodeZero\Translator\FileLoader\LoadedFile;
use CodeZero\Translator\Tests\FileTestCase;

class LaravelFileLoaderTest extends FileTestCase
{
    /** @test */
    public function it_loads_translation_from_translation_files()
    {
        $this->createTranslationFile('en/auth.php', [
            'login' => ['password' => 'password incorrect'],
            'session' => ['expired' => 'session expired'],
        ]);
        $this->createTranslationFile('nl/auth.php', [
            'login' => ['password' => 'wachtwoord onjuist'],
        ]);
        $this->createTranslationFile('vendor/package/en/langfile.php', [
            'some' => ['key' => 'vendor translation'],
        ]);
        $this->createTranslationFile('nl.json', [
            'This is a JSON translation.' => 'Dit is een JSON vertaling.',
        ]);
        $this->createTranslationFile('en.json', [
            'This is a JSON translation.' => 'This is a JSON translation.',
        ]);

        $loader = new LaravelFileLoader();
        $loadedFiles = $loader->load($this->getLangPath());

        $this->assertCount(3, $loadedFiles);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[0]);
        $this->assertEquals(null, $loadedFiles[0]->vendor);
        $this->assertEquals('_json', $loadedFiles[0]->filename);
        $this->assertEquals([
            'This is a JSON translation.' => [
                'en' => 'This is a JSON translation.',
                'nl' => 'Dit is een JSON vertaling.',
            ],
        ], $loadedFiles[0]->translations);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[1]);
        $this->assertEquals(null, $loadedFiles[1]->vendor);
        $this->assertEquals('auth', $loadedFiles[1]->filename);
        $this->assertEquals([
            'login.password' => [
                'en' => 'password incorrect',
                'nl' => 'wachtwoord onjuist',
            ],
            'session.expired' => [
                'en' => 'session expired',
            ],
        ], $loadedFiles[1]->translations);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[2]);
        $this->assertEquals('package', $loadedFiles[2]->vendor);
        $this->assertEquals('langfile', $loadedFiles[2]->filename);
        $this->assertEquals([
            'some.key' => [
                'en' => 'vendor translation',
            ],
        ], $loadedFiles[2]->translations);
    }

    /** @test */
    public function it_does_not_load_empty_translations_by_default()
    {
        $this->createTranslationFile('en/auth.php', [
            'login' => ['password' => 'password incorrect'],
            'session' => ['expired' => ''],
        ]);
        $this->createTranslationFile('vendor/package/en/langfile.php', []);
        $this->createTranslationFile('nl.json', [
            'This is a JSON translation.' => 'Dit is een JSON vertaling.',
        ]);
        $this->createTranslationFile('en.json', []);

        $loader = new LaravelFileLoader();
        $loadedFiles = $loader->load($this->getLangPath());

        $this->assertCount(2, $loadedFiles);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[0]);
        $this->assertEquals(null, $loadedFiles[0]->vendor);
        $this->assertEquals('_json', $loadedFiles[0]->filename);
        $this->assertEquals([
            'This is a JSON translation.' => [
                'nl' => 'Dit is een JSON vertaling.',
            ],
        ], $loadedFiles[0]->translations);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[1]);
        $this->assertEquals(null, $loadedFiles[1]->vendor);
        $this->assertEquals('auth', $loadedFiles[1]->filename);
        $this->assertEquals([
            'login.password' => [
                'en' => 'password incorrect',
            ],
        ], $loadedFiles[1]->translations);
    }

    /** @test */
    public function it_can_load_empty_translations()
    {
        $this->createTranslationFile('en/auth.php', [
            'login' => ['password' => 'password incorrect'],
            'session' => ['expired' => ''],
        ]);
        $this->createTranslationFile('vendor/package/en/langfile.php', []);
        $this->createTranslationFile('nl.json', [
            'This is a JSON translation.' => 'Dit is een JSON vertaling.',
        ]);
        $this->createTranslationFile('en.json', []);

        $loader = new LaravelFileLoader();
        $loadedFiles = $loader->includeEmpty()->load($this->getLangPath());

        $this->assertCount(3, $loadedFiles);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[0]);
        $this->assertEquals(null, $loadedFiles[0]->vendor);
        $this->assertEquals('_json', $loadedFiles[0]->filename);
        $this->assertEquals([
            'This is a JSON translation.' => [
                'nl' => 'Dit is een JSON vertaling.',
            ],
        ], $loadedFiles[0]->translations);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[1]);
        $this->assertEquals(null, $loadedFiles[1]->vendor);
        $this->assertEquals('auth', $loadedFiles[1]->filename);
        $this->assertEquals([
            'login.password' => [
                'en' => 'password incorrect',
            ],
            'session.expired' => [
                'en' => '',
            ],
        ], $loadedFiles[1]->translations);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[2]);
        $this->assertEquals('package', $loadedFiles[2]->vendor);
        $this->assertEquals('langfile', $loadedFiles[2]->filename);
        $this->assertEquals([], $loadedFiles[2]->translations);
    }

    /** @test */
    public function it_can_load_specific_locales()
    {
        $this->createTranslationFile('en/filename.php', [
            'key' => 'translation [en]',
        ]);
        $this->createTranslationFile('nl/filename.php', [
            'key' => 'translation [nl]',
        ]);
        $this->createTranslationFile('fr/filename.php', [
            'key' => 'translation [fr]',
        ]);
        $this->createTranslationFile('en.json', [
            'key' => 'translation [en]',
        ]);
        $this->createTranslationFile('nl.json', [
            'key' => 'translation [nl]',
        ]);
        $this->createTranslationFile('fr.json', [
            'key' => 'translation [fr]',
        ]);

        $loader = new LaravelFileLoader();
        $loadedFiles = $loader->onlyLocales(['en', 'nl'])->load($this->getLangPath());

        $this->assertCount(2, $loadedFiles);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[0]);
        $this->assertEquals(null, $loadedFiles[0]->vendor);
        $this->assertEquals('_json', $loadedFiles[0]->filename);
        $this->assertEquals([
            'key' => [
                'en' => 'translation [en]',
                'nl' => 'translation [nl]',
            ],
        ], $loadedFiles[0]->translations);

        $this->assertInstanceOf(LoadedFile::class, $loadedFiles[1]);
        $this->assertEquals(null, $loadedFiles[1]->vendor);
        $this->assertEquals('filename', $loadedFiles[1]->filename);
        $this->assertEquals([
            'key' => [
                'en' => 'translation [en]',
                'nl' => 'translation [nl]',
            ],
        ], $loadedFiles[1]->translations);
    }
}
