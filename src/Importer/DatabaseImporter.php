<?php

namespace CodeZero\Translator\Importer;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;

class DatabaseImporter implements Importer
{
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
    protected $shouldAddMissing = false;

    /**
     * Import empty translations.
     *
     * @var bool
     */
    protected $shouldImportEmpty = false;

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
     * @param bool $add
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function addMissing($add = true)
    {
        $this->shouldAddMissing = $add;

        return $this;
    }

    /**
     * Import empty translations.
     *
     * @param bool $import
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function importEmpty($import = true)
    {
        $this->shouldImportEmpty = $import;

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

        if ($translationFile->exists && ! $this->shouldAddMissing && ! $this->shouldReplaceExisting) {
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
        if ( ! $translation && ! $this->shouldImportEmpty) {
            return;
        }

        $existingTranslation = $translationKey->getTranslation($locale);

        if ( ! $translationFile->wasRecentlyCreated && ! $existingTranslation && ! $this->shouldAddMissing) {
            return;
        }

        if ( ! $translationFile->wasRecentlyCreated && $existingTranslation && ! $this->shouldReplaceExisting) {
            return;
        }

        $translationKey->addTranslation($locale, $translation);
        $translationKey->save();
    }
}
