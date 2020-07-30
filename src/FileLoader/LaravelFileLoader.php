<?php

namespace CodeZero\Translator\FileLoader;

use CodeZero\Translator\Exceptions\PathDoesNotExist;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class LaravelFileLoader implements FileLoader
{
    /**
     * Array of LoadedFile instances.
     *
     * @var array
     */
    protected $loadedFiles;

    /**
     * Load translations.
     *
     * @param string|null $langPath
     *
     * @return array
     * @throws \CodeZero\Translator\Exceptions\PathDoesNotExist
     */
    public function load($langPath = null)
    {
        $this->ensureDirectoryExists($langPath);
        $this->loadedFiles = [];

        $libraryPaths = $this->listLibraryPaths($langPath);

        foreach ($libraryPaths as $libraryPath) {
            $vendor = $this->getVendorNameFromPath($libraryPath);
            $this->loadJsonFiles($libraryPath, $vendor);
            $this->loadPhpFiles($libraryPath, $vendor);
        }

        return array_values($this->loadedFiles);
    }

    /**
     * Load JSON translation files.
     *
     * @param string $langPath
     * @param string|null $vendor
     *
     * @return void
     */
    protected function loadJsonFiles($langPath, $vendor)
    {
        if ($vendor !== null) {
            return;
        }

        $files = $this->listTranslationFiles($langPath, 'json');

        foreach ($files as $file) {
            $loadedFile = $this->findOrMakeLoadedFile('_json', $vendor);
            $translations = json_decode(File::get($file), true);
            $locale = File::name($file);

            foreach ($translations as $key => $translation) {
                $loadedFile->addTranslation($key, $locale, $translation);
            }
        }
    }

    /**
     * Load PHP translation files.
     *
     * @param string $libraryPath
     * @param string|null $vendor
     *
     * @return void
     */
    protected function loadPhpFiles($libraryPath, $vendor)
    {
        $localePaths = $this->listLocalesInLibrary($libraryPath);

        foreach ($localePaths as $localePath) {
            $files = $this->listTranslationFiles($localePath, 'php');
            $locale = File::basename($localePath);

            foreach ($files as $file) {
                $loadedFile = $this->findOrMakeLoadedFile(File::name($file), $vendor);
                $translations = Arr::dot(include $file);

                foreach ($translations as $key => $translation) {
                    $loadedFile->addTranslation($key, $locale, $translation);
                }
            }
        }
    }

    /**
     * Find a LoadedFile instance or make a new one if it doesn't exist.
     *
     * @param string $filename
     * @param string|null $vendor
     *
     * @return \CodeZero\Translator\FileLoader\LoadedFile
     */
    protected function findOrMakeLoadedFile($filename, $vendor = null)
    {
        $index = "{$vendor}::{$filename}";

        if ( ! array_key_exists($index, $this->loadedFiles)) {
            $this->loadedFiles[$index] = LoadedFile::make($filename, $vendor);
        }

        return $this->loadedFiles[$index];
    }

    /**
     * List all paths that should be crawled for translation files.
     * This is the root language path and any vendor subdirectories.
     * Example output:
     * [
     *     '/path/to/resources/lang',
     *     '/path/to/resources/lang/vendor/package-a',
     *     '/path/to/resources/lang/vendor/package-b',
     * ]
     *
     * @param string $langPath
     *
     * @return array
     */
    protected function listLibraryPaths($langPath)
    {
        $vendorPath = $langPath.'/vendor';

        if ( ! File::isDirectory($vendorPath)) {
            return [$langPath];
        }

        return $this->listSubdirectories($vendorPath)
            ->prepend($langPath)
            ->toArray();
    }

    /**
     * List the paths to the locale directories in the given library.
     * Example output:
     * [
     *     '/path/to/resources/lang/vendor/package-x/en',
     *     '/path/to/resources/lang/vendor/package-x/nl',
     * ]
     *
     * @param string $libraryPath
     *
     * @return array
     */
    protected function listLocalesInLibrary($libraryPath)
    {
        return $this->listSubdirectories($libraryPath)
            ->reject(function ($path) {
                return File::basename($path) === 'vendor';
            })->toArray();
    }

    /**
     * List all files of the specified type in the given path.
     *
     * @param string $path
     * @param string $type
     *
     * @return array
     */
    protected function listTranslationFiles($path, $type)
    {
        return $this->listFiles($path)
            ->filter(function ($filePath) use ($type) {
                return File::extension($filePath) === $type;
            })->toArray();
    }

    /**
     * Get the vendor name from the path if it is in the vendor directory.
     * If it is the root "lang" resource path, vendor is null.
     *
     * @param string $libraryPath
     *
     * @return string|null
     */
    protected function getVendorNameFromPath($libraryPath)
    {
        return File::basename(File::dirname($libraryPath)) === 'vendor'
            ? File::basename($libraryPath)
            : null;
    }

    /**
     * List all files in a given path (not recursive).
     *
     * @param string $path
     *
     * @return \Illuminate\Support\Collection
     */
    protected function listFiles($path)
    {
        return Collection::make(File::files($path));
    }

    /**
     * List all subdirectories of a given path (not recursive).
     *
     * @param string $path
     *
     * @return \Illuminate\Support\Collection
     */
    protected function listSubdirectories($path)
    {
        return Collection::make(File::directories($path));
    }

    /**
     * Check if the given path is a directory.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function isDirectory($path)
    {
        return File::isDirectory($path);
    }

    /**
     * Ensure that the given path is a directory.
     *
     * @param string $path
     *
     * @throws \CodeZero\Translator\Exceptions\PathDoesNotExist
     */
    protected function ensureDirectoryExists($path)
    {
        if ( ! $this->isDirectory($path)) {
            throw new PathDoesNotExist("The directory \"{$path}\" does not exist.");
        }
    }
}
