<?php

namespace CodeZero\Translator\FileLoader;

class TranslationFile
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
    public $file;

    /**
     * Translations in this file.
     *
     * @var array
     */
    public $translations;

    /**
     * Create a new TranslationFile instance.
     *
     * @param string $file
     * @param string|null $vendor
     */
    public function __construct($file, $vendor = null)
    {
        $this->vendor = $vendor;
        $this->file = $file;
    }

    /**
     * Add a translation in the specified locale for a given key.
     *
     * @param string $key
     * @param string $locale
     * @param string $translation
     *
     * @return \CodeZero\Translator\FileLoader\TranslationFile
     */
    public function addTranslation($key, $locale, $translation)
    {
        $this->translations[$key] = $this->translations[$key] ?? [];
        $this->translations[$key][$locale] = $translation;

        return $this;
    }
}
