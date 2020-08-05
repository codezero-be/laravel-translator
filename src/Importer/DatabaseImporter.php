<?php

namespace CodeZero\Translator\Importer;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;

class DatabaseImporter implements Importer
{
    /**
     * Locales that should be imported.
     *
     * @var array|null
     */
    protected $locales;

    /**
     * Replace existing translations.
     *
     * @var bool
     */
    protected $shouldReplaceExisting = false;

    /**
     * Add missing translations to existing translation keys.
     *
     * @var bool
     */
    protected $shouldFillMissing = false;

    /**
     * Import empty translations.
     *
     * @var bool
     */
    protected $shouldIncludeEmpty = false;

    /**
     * Set the locales that should be imported.
     * By default it will import all locales in
     * the files passed to the import method.
     *
     * @param array|null $locales
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function onlyLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Replace existing translations.
     *
     * @param bool $replace
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function replaceExisting($replace = true)
    {
        $this->shouldReplaceExisting = $replace;

        return $this;
    }

    /**
     * Add missing translations to existing translation keys.
     *
     * @param bool $missing
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function fillMissing($missing = true)
    {
        $this->shouldFillMissing = $missing;

        return $this;
    }

    /**
     * Import empty translations.
     *
     * @param bool $empty
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function includeEmpty($empty = true)
    {
        $this->shouldIncludeEmpty = $empty;

        return $this;
    }

    /**
     * Import translations into the database.
     *
     * @param array $files
     *
     * @return void
     */
    public function import($files)
    {
        foreach ($files as $file) {
            $this->importTranslationFile((array) $file);
        }
    }

    /**
     * Import translations of the given file.
     *
     * @param array $file
     *
     * @return void
     */
    protected function importTranslationFile($file)
    {
        $translationFile = TranslationFile::firstOrNew([
            'vendor' => $file['vendor'],
            'filename' => $file['filename'],
        ]);

        $translationKeys = [];

        foreach ($file['translations'] as $key => $translations) {
            $translationKeys[] = $this->findOrMakeTranslationKey($translationFile, $key, $translations);
        }

        $translationKeys = array_filter($translationKeys);

        if (count($translationKeys) === 0) {
            return;
        }

        $translationFile->save();
        $translationFile->translationKeys()->saveMany($translationKeys);
    }

    /**
     * Find or make a TranslationKey and add translations.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $translationFile
     * @param string $key
     * @param array $translations
     *
     * @return \CodeZero\Translator\Models\TranslationKey
     */
    protected function findOrMakeTranslationKey($translationFile, $key, $translations)
    {
        $action = $translationFile->exists ? 'firstOrNew' : 'make';

        $translationKey = TranslationKey::$action([
            'file_id' => $translationFile->id,
            'key' => $key,
        ]);

        foreach ($translations as $locale => $translation) {
            $this->addTranslation($translationKey, $locale, $translation);
        }

        return count($translationKey->getTranslations()) > 0
            ? $translationKey
            : null;
    }

    /**
     * Add a translation to the given key in a specific locale.
     *
     * @param \CodeZero\Translator\Models\TranslationKey $translationKey
     * @param string $locale
     * @param string $translation
     *
     * @return void
     */
    protected function addTranslation($translationKey, $locale, $translation)
    {
        $existingTranslation = $translationKey->getTranslation($locale);

        if ($this->isValidLocale($locale) && $this->isValidTranslation($translationKey, $existingTranslation, $translation)) {
            $translationKey->addTranslation($locale, $translation);
        }
    }

    /**
     * Check if the given locale is eligible for import.
     *
     * @param string $locale
     *
     * @return bool
     */
    protected function isValidLocale($locale)
    {
        return ! $this->locales || in_array($locale, $this->locales);
    }

    /**
     * Check if the given translation is eligible for import.
     *
     * @param \CodeZero\Translator\Models\TranslationKey $translationKey
     * @param string|null $existingTranslation
     * @param string|null $translation
     *
     * @return bool
     */
    protected function isValidTranslation($translationKey, $existingTranslation, $translation)
    {
        return ($translation || $this->shouldIncludeEmpty)
            && ( ! $translationKey->exists
                || (($existingTranslation && $this->shouldReplaceExisting)
                    || ( ! $existingTranslation && $this->shouldFillMissing)));
    }
}
