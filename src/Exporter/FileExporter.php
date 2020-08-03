<?php

namespace CodeZero\Translator\Exporter;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\VarExporter\VarExporter;

class FileExporter implements Exporter
{
    /**
     * Locales that should be exported.
     *
     * @var array|null
     */
    protected $locales;

    /**
     * Export missing or empty translations.
     *
     * @var bool
     */
    protected $shouldIncludeMissing;

    /**
     * Set the locales that should be exported.
     * By default it will export whatever is passed to the export method.
     *
     * @param array|null $locales
     *
     * @return \CodeZero\Translator\Exporter\Exporter
     */
    public function onlyLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Export missing or empty translations.
     *
     * @param bool $missing
     *
     * @return \CodeZero\Translator\Exporter\Exporter
     */
    public function includeMissing($missing = true)
    {
        $this->shouldIncludeMissing = $missing;

        return $this;
    }

    /**
     * Export translation files to the destination directory.
     *
     * @param array $translationFiles
     * @param string $destination
     *
     * @return void
     */
    public function export($translationFiles, $destination)
    {
        $explicitLocales = $this->determineLocales($translationFiles);
        $translations = $this->sortTranslationsPerExportedFile($translationFiles, $explicitLocales);

        $this->prepareDestinationDirectory($destination);

        foreach ($translations as $path => $values) {
            $this->exportFile("{$destination}/{$path}", $values);
        }
    }

    /**
     * Determine if there are any locales that should be exported explicitly.
     * Returning null will export any locales in the provided translations.
     * If `$this->locales` is not configured and missing or empty translations
     * should be exported, all locales in all translation keys will be indexed.
     *
     * @param array $translationFiles
     *
     * @return array|null
     */
    protected function determineLocales($translationFiles)
    {
        if ($this->locales) {
            return $this->locales;
        }

        if ( ! $this->shouldIncludeMissing) {
            return null;
        }

        $locales = [];

        foreach ($translationFiles as $file) {
            foreach ($file->translationKeys as $key) {
                $locales = array_merge($locales, array_keys($key->translations));
            }
        }

        return array_unique($locales);
    }

    /**
     * Build translation file paths and sort translations accordingly.
     * This will result in an array like this:
     * [
     *     'en/filename.php' => [
     *         'key-a' => 'translation a [en]',
     *         'key-b' => 'translation b [en]',
     *     ],
     *     'nl/filename.php' => [
     *         'key-a' => 'translation a [nl]',
     *         'key-b' => 'translation b [nl]',
     *     ],
     * ]
     *
     * @param array $translationFiles
     * @param array|null $explicitLocales
     *
     * @return array
     */
    protected function sortTranslationsPerExportedFile($translationFiles, $explicitLocales)
    {
        $translations = [];

        foreach ($translationFiles as $file) {
            foreach ($file->translationKeys as $key) {
                $locales = $explicitLocales ?: array_keys($key->translations);

                foreach ($locales as $locale) {
                    $translation = $key->translations[$locale] ?? '';

                    if ( ! $translation && ! $this->shouldIncludeMissing) {
                        continue;
                    }

                    $isJson = $file->filename === '_json';
                    $path = $this->buildRelativeFilePath($locale, $file->filename, $file->vendor, $isJson);
                    $existingKeys = $translations[$path] ?? [];
                    $translations[$path] = $this->addTranslation($existingKeys, $key->key, $translation, $isJson);
                }
            }
        }

        return $translations;
    }

    /**
     * Build the path to the exported translation file,
     * relative to the destination directory.
     *
     * @param string $locale
     * @param string $filename
     * @param string|null $vendor
     * @param bool $isJson
     *
     * @return string
     */
    protected function buildRelativeFilePath($locale, $filename, $vendor, $isJson)
    {
        $path = $isJson
            ? "{$locale}.json"
            : "{$locale}/{$filename}.php";

        return $vendor ? "vendor/{$vendor}/{$path}" : $path;
    }

    /**
     * Add the new key/translation pair to the existing keys.
     *
     * @param array $existingKeys
     * @param string $newKey
     * @param string $translation
     * @param bool $isJson
     *
     * @return array
     */
    protected function addTranslation($existingKeys, $newKey, $translation, $isJson)
    {
        if ( ! $isJson) {
            // Expand key with dot notation to multidimensional array.
            return Arr::add($existingKeys, $newKey, $translation);
        }

        $existingKeys[$newKey] = $translation;

        return $existingKeys;
    }

    /**
     * Create and clean the destination directory.
     *
     * @param string $destination
     *
     * @return void
     */
    protected function prepareDestinationDirectory($destination)
    {
        File::ensureDirectoryExists($destination);
        File::cleanDirectory($destination);
    }

    /**
     * Export a file.
     *
     * @param string $path
     * @param array $values
     *
     * @return void
     */
    protected function exportFile($path, $values)
    {
        File::ensureDirectoryExists(File::dirname($path));
        $type = File::extension($path);
        File::put($path, $this->convertArrayToString($type, $values));
    }

    /**
     * Convert the translations array to a PHP or JSON string for export.
     *
     * @param string $type
     * @param array $array
     *
     * @return string
     */
    protected function convertArrayToString($type, $array)
    {
        if ($type === 'json') {
            return json_encode($array, JSON_PRETTY_PRINT);
        }

        $content = VarExporter::export($array);

        return "<?php\n\nreturn {$content};";
    }
}
