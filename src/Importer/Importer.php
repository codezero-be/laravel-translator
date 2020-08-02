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
     * @param bool $add
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function addMissing($add = true);

    /**
     * Import empty translations.
     *
     * @param bool $import
     *
     * @return \CodeZero\Translator\Importer\Importer
     */
    public function importEmpty($import = true);

    /**
     * Import translations into the database.
     *
     * @param array $files
     *
     * @return void
     */
    public function import($files);
}
