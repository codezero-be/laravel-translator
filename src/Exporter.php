<?php

namespace CodeZero\Translator;

use Artisan;
use CodeZero\Translator\Models\TranslationFile;
use File;

class Exporter
{
    /**
     * Destination path for the generated language files.
     *
     * @var string
     */
    protected $destination;

    /**
     * Path to the PHP CS Fixer.
     *
     * @var string
     */
    protected $fixer;

    /**
     * Array of exported translations.
     *
     * @var array
     */
    protected $libraries;

    /**
     * Export all database translations to language files.
     * The destination folder will be cleared before export.
     *
     * @param null|string $destination
     * @param null|string $fixer
     *
     * @return array
     */
    public function export($destination = null, $fixer = null)
    {
        $this->destination = $destination ?: resource_path('lang');
        $this->fixer = $fixer ?: base_path('vendor/bin/php-cs-fixer');
        $this->libraries = [];

        $translationFiles = TranslationFile::with('translations')->get();

        $translationFiles->each(function ($translationFile) {
            $this->addTranslationsToArray($translationFile);
        });

        $this->clearDestination();
        $this->exportLibraries();
        $this->formatLanguageFiles($this->libraries);

        return $this->libraries;
    }

    /**
     * Add all Translations in a TranslationFile to the libraries array.
     *
     * @param string $translationFile
     *
     * @return void
     */
    protected function addTranslationsToArray($translationFile)
    {
        $translationFile->translations->each(function ($translation) use ($translationFile) {
            foreach ($translation->getTranslations('body') as $language => $body) {
                $this->addTranslationToArray(
                    $translationFile->package,
                    $language,
                    $translationFile->name,
                    $translation->key,
                    $body
                );
            }
        });
    }

    /**
     * Add Translation details to the libraries array.
     * The resulting array will look like this:
     * $array[$package][$language][$filename][$keyPartA][$keyPartB] = $body
     *
     * @param string $package
     * @param string $language
     * @param string $filename
     * @param string $key
     * @param string $body
     *
     * @return void
     */
    protected function addTranslationToArray($package, $language, $filename, $key, $body)
    {
        $package = $package ?: 'root';

        $this->libraries = $this->initializeMultiDimensionalArray(
            [$package, $language, $filename],
            $this->expandTranslationToArray($key, $body),
            $this->libraries
        );
    }

    /**
     * Expand a Translation key to a multidimensional array and assign the value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return array
     */
    protected function expandTranslationToArray($key, $value = [])
    {
        $keys = explode('.', $key);

        return $this->initializeMultiDimensionalArray($keys, $value);
    }

    /**
     * Initialize a nested array for each provided key.
     * Assign the given value at the end.
     * Merge recursively with optional existing array.
     *
     * $array = initializeMultiDimensionalArray(['a', 'b', 'c'], 'Hello World');
     * gives you:
     * $array['a']['b']['c'] === 'Hello World';
     *
     * @param array $keys
     * @param mixed $value
     * @param array $mergeWith
     *
     * @return array
     */
    protected function initializeMultiDimensionalArray($keys, $value = [], $mergeWith = [])
    {
        $array = [];
        $key = array_shift($keys);

        $array[$key] = empty($keys) ? $value : $this->initializeMultiDimensionalArray($keys, $value);

        return array_merge_recursive($mergeWith, $array);
    }

    /**
     * Export all libraries to their respective subdirectories.
     *
     * @return void
     */
    protected function exportLibraries()
    {
        foreach ($this->libraries as $library => $languages) {
            $path = $this->destination;

            if ($library !== 'root') {
                $path .= "/vendor/{$library}";
            }

            $this->exportLanguages($path, $languages);
        }
    }

    /**
     * Export language folders in a library to their respective subdirectories.
     *
     * @param string $path
     * @param array $languages
     *
     * @return void
     */
    protected function exportLanguages($path, $languages)
    {
        foreach ($languages as $language => $files) {
            $this->exportFiles("{$path}/{$language}", $files);
        }
    }

    /**
     * Export language files to their respective directory.
     *
     * @param string $path
     * @param array $files
     *
     * @return void
     */
    protected function exportFiles($path, $files)
    {
        $this->createDirectoryIfNotExist($path);

        foreach ($files as $filename => $contents) {
            $array = var_export($contents, true);
            $array = $this->indentArrayByFourSpacesInsteadOfTwo($array);
            $php = "<?php\n\nreturn {$array};\n";
            File::put("{$path}/{$filename}.php", $php);
        }
    }

    /**
     * Indent the given array source code with 4 spaces instead of 2.
     * var_export indents with 2 spaces and PHP CS Fixer cannot convert indentation.
     *
     * @param string $array
     * @param int $depth
     *
     * @return string
     */
    protected function indentArrayByFourSpacesInsteadOfTwo($array, $depth = 5)
    {
        $match = "  ";

        while ($depth > 0) {
            $array = str_replace("\n{$match}", "\n{$match}  ", $array);
            $match = $match . "    ";
            $depth--;
        }

        return $array;
    }

    /**
     * Format the generated language files.
     * This use the short array syntax and proper indentation.
     *
     * @param array $libraries
     *
     * @return void
     */
    protected function formatLanguageFiles($libraries)
    {
        foreach ($libraries as $package => $translations) {
            $path = $this->destination;

            if ($package !== 'root') {
                $path .= "/vendor/{$package}";
            }

            Artisan::call('lang:format', [
                'path' => $path,
                'fixer' => $this->fixer,
            ]);
        }
    }

    /**
     * Clear the destination directory.
     *
     * @return void
     */
    protected function clearDestination()
    {
        File::cleanDirectory($this->destination);
    }

    /**
     * Create the directory at the given path if it does not exist.
     *
     * @param string $path
     *
     * @return void
     */
    protected function createDirectoryIfNotExist($path)
    {
        if ( ! File::isDirectory($path)) {
            File::makeDirectory($path, 493, true);
        }
    }
}
