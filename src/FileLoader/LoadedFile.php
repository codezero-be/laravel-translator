<?php

namespace CodeZero\Translator\FileLoader;

class LoadedFile
{
    /**
     * Vendor name, if any.
     *
     * @var string|null
     */
    public $vendor;

    /**
     * The filename to which the translations belong.
     * "_json" if it is a JSON file.
     *
     * @var string
     */
    public $filename;

    /**
     * Translations in this file.
     *
     * @var array
     */
    public $translations;

    /**
     * Create a new LoadedFile instance.
     *
     * @param string $filename
     * @param string|null $vendor
     * @param array $translations
     */
    public function __construct($filename, $vendor = null, $translations = [])
    {
        $this->vendor = $vendor;
        $this->filename = $filename;
        $this->translations = $translations;
    }

    /**
     * Create a new LoadedFile instance.
     *
     * @param string $filename
     * @param string|null $vendor
     * @param array $translations
     *
     * @return \CodeZero\Translator\FileLoader\LoadedFile
     */
    public static function make($filename, $vendor = null, $translations = [])
    {
        return new static($filename, $vendor, $translations);
    }

    /**
     * Add a translation in the specified locale for a given key.
     *
     * @param string $key
     * @param string $locale
     * @param string $translation
     *
     * @return \CodeZero\Translator\FileLoader\LoadedFile
     */
    public function addTranslation($key, $locale, $translation)
    {
        $this->translations[$key] = $this->translations[$key] ?? [];
        $this->translations[$key][$locale] = $translation;

        return $this;
    }

    /**
     * Check if this file has any translations.
     *
     * @return bool
     */
    public function hasTranslations()
    {
        return count($this->translations) > 0;
    }

    /**
     * Convert this object to an associative array.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this;
    }
}
