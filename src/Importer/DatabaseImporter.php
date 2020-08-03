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
     * Add missing translations to existing translation files.
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
     * Add missing translations to existing translation files.
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

        if ($this->shouldRejectTranslationFile($translationFile)) {
            return;
        }

        $translationFile->save();

        foreach ($file['translations'] as $key => $translations) {
            $this->importTranslationKey($translationFile, $key, $translations);
        }
    }

    /**
     * Import translations of the given key and file.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $translationFile
     * @param string $key
     * @param array $translations
     *
     * @return void
     */
    protected function importTranslationKey($translationFile, $key, $translations)
    {
        $translationKey = TranslationKey::firstOrNew([
            'file_id' => $translationFile->id,
            'key' => $key,
        ]);

        foreach ($translations as $locale => $translation) {
            $this->importTranslation($translationFile, $translationKey, $locale, $translation);
        }
    }

    /**
     * Import a translation of the given key and file in a specific locale.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $translationFile
     * @param \CodeZero\Translator\Models\TranslationKey $translationKey
     * @param string $locale
     * @param string $translation
     *
     * @return void
     */
    protected function importTranslation($translationFile, $translationKey, $locale, $translation)
    {
        $existingTranslation = $translationKey->getTranslation($locale);

        if ($this->shouldRejectTranslation($translation)
            || $this->shouldRejectLocale($locale)
            || $this->shouldRejectToFillMissing($translationFile, $existingTranslation)
            || $this->shouldRejectToReplaceExisting($translationFile, $existingTranslation)
        ) {
            return;
        }

        $translationKey->addTranslation($locale, $translation);
        $translationKey->save();
    }

    /**
     * Check if the given translation file should be handled at all.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $translationFile
     *
     * @return bool
     */
    protected function shouldRejectTranslationFile($translationFile)
    {
        return $translationFile->exists && ! $this->shouldFillMissing && ! $this->shouldReplaceExisting;
    }

    /**
     * Check if the translation is empty and
     * we need to reject empty values.
     *
     * @param string $translation
     *
     * @return bool
     */
    protected function shouldRejectTranslation($translation)
    {
        return ! $translation && ! $this->shouldIncludeEmpty;
    }

    /**
     * Check if specific locales should be imported and
     * if the given locale is not in that list.
     *
     * @param string $locale
     *
     * @return bool
     */
    protected function shouldRejectLocale($locale)
    {
        return $this->locales && ! in_array($locale, $this->locales);
    }

    /**
     * Check if an existing translation file is missing a translation
     * and if we should not fill missing translations.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $translationFile
     * @param string|null $existingTranslation
     *
     * @return bool
     */
    protected function shouldRejectToFillMissing($translationFile, $existingTranslation)
    {
        return ! $translationFile->wasRecentlyCreated && ! $existingTranslation && ! $this->shouldFillMissing;
    }

    /**
     * Check if if a translation already exists and we
     * should not replace existing translations.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $translationFile
     * @param string|null $existingTranslation
     *
     * @return bool
     */
    protected function shouldRejectToReplaceExisting($translationFile, $existingTranslation)
    {
        return ! $translationFile->wasRecentlyCreated && $existingTranslation && ! $this->shouldReplaceExisting;
    }
}
