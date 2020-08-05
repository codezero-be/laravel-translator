<?php

namespace CodeZero\Translator\Exporter;

interface Exporter
{
    /**
     * Set the locales that should be exported.
     * By default it will export whatever is passed to the export method.
     *
     * @param array|null $locales
     *
     * @return \CodeZero\Translator\Exporter\Exporter
     */
    public function onlyLocales($locales);

    /**
     * Export missing or empty translations.
     *
     * @param bool $empty
     *
     * @return \CodeZero\Translator\Exporter\Exporter
     */
    public function includeEmpty($empty = true);

    /**
     * Export translation files to the destination directory.
     *
     * @param array $translationFiles
     * @param string $destination
     *
     * @return void
     */
    public function export($translationFiles, $destination);
}
