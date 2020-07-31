<?php

namespace CodeZero\Translator\Tests;

use Illuminate\Support\Facades\File;

class FileTestCase extends TestCase
{
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
        return rtrim(__DIR__ . '/_test-files/lang/' .$path, '/');
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
