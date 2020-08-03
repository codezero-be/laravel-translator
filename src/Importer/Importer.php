<?php

namespace CodeZero\Translator\Importer;

interface Importer
{
    /**
     * Replace existing translations.
     *
     * @param bool $replace
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function replaceExisting($replace = true);

    /**
     * Add missing translations to existing translation files.
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
