<?php

namespace CodeZero\Translator\Importer;

interface Importer
{
    /**
     * Set the locales that should be imported.
     * By default it will import all locales in
     * the files passed to the import method.
     *
     * @param array|null $locales
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function onlyLocales($locales);

    /**
     * Replace existing translations.
     *
     * @param bool $replace
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function replaceExisting($replace = true);

    /**
     * Add missing translations to existing translation keys.
     *
     * @param bool $missing
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function fillMissing($missing = true);

    /**
     * Import empty translations.
     *
     * @param bool $empty
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function includeEmpty($empty = true);

    /**
     * Import translations into the database.
     *
     * @param array $files
     *
     * @return void
     */
    public function import($files);
}
