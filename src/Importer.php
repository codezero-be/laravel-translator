<?php

namespace CodeZero\Translator;

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;

class Importer
{
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
     * Import a translation file.
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

        $translationFile->save();

        foreach ($file['translations'] as $key => $translations) {
            $this->importTranslations($translationFile, $key, $translations);
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
    protected function importTranslations($translationFile, $key, $translations)
    {
        $translationKey = TranslationKey::firstOrNew([
            'file_id' => $translationFile->id,
            'key' => $key,
        ]);

        if ( ! $translationKey->exists) {
            $translationKey->translations = $translations;
            $translationKey->save();
        }
    }
}
