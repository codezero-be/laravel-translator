<?php

namespace CodeZero\Translator\Tests\Unit\FileLoader;

use CodeZero\Translator\FileLoader\LaravelFileLoader;
use CodeZero\Translator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class LaravelFileLoaderTest extends TestCase
{
    /** @test */
    public function it_loads_translation_from_translation_files()
    {
        $this->createTranslationFile('en/auth.php', [
            'login' => [
                'password' => 'password incorrect',
            ],
            'session' => [
                'expired' => 'session expired',
            ],
        ]);
        $this->createTranslationFile('nl/auth.php', [
            'login' => [
                'password' => 'wachtwoord onjuist',
            ],
        ]);
        $this->createTranslationFile('vendor/package/en/langfile.php', [
            'some' => [
                'key' => 'vendor translation',
            ],
        ]);
        $this->createTranslationFile('nl.json', [
            'This is a JSON translation.' => 'Dit is een JSON vertaling.',
        ]);
        $this->createTranslationFile('en.json', [
            'This is a JSON translation.' => 'This is a JSON translation.',
        ]);

        $loader = new LaravelFileLoader();
        $translations = $loader->load($this->getLangPath());

        $this->assertCount(3, $translations);

        $this->assertEquals(null, $translations[0]->vendor);
        $this->assertEquals('_json', $translations[0]->file);
        $this->assertEquals([
            'This is a JSON translation.' => [
                'en' => 'This is a JSON translation.',
                'nl' => 'Dit is een JSON vertaling.',
            ],
        ], $translations[0]->translations);

        $this->assertEquals(null, $translations[1]->vendor);
        $this->assertEquals('auth', $translations[1]->file);
        $this->assertEquals([
            'login.password' => [
                'en' => 'password incorrect',
                'nl' => 'wachtwoord onjuist',
            ],
            'session.expired' => [
                'en' => 'session expired',
            ],
        ], $translations[1]->translations);

        $this->assertEquals('package', $translations[2]->vendor);
        $this->assertEquals('langfile', $translations[2]->file);
        $this->assertEquals([
            'some.key' => [
                'en' => 'vendor translation',
            ],
        ], $translations[2]->translations);
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearTestDirectory();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->clearTestDirectory();

        parent::tearDown();
    }

    /**
     * Get the path to the language folder inside the testing directory.
     *
     * @param string|null $path
     *
     * @return string
     */
    protected function getLangPath($path = null)
    {
        return rtrim(__DIR__ . '/../../_test-files/lang/' .$path, '/');
    }

    /**
     * Delete the test directory.
     *
     * @return void
     */
    protected function clearTestDirectory()
    {
        File::deleteDirectory($this->getLangPath());
    }

    /**
     * Create a dummy translation file in the test directory.
     *
     * @param string $path
     * @param array $translations
     *
     * @return void
     */
    protected function createTranslationFile($path, $translations)
    {
        $directories = explode('/', $path);
        $file = array_pop($directories);
        $directory = $this->getLangPath(join('/', $directories));

        $content = File::extension($file) === 'json'
            ? json_encode($translations, JSON_PRETTY_PRINT)
            : '<?php return '.var_export($translations, true).';';

        File::ensureDirectoryExists($directory);
        File::put("{$directory}/{$file}", $content);
    }
}
