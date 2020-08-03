<?php

namespace CodeZero\Translator\FileLoader;

interface FileLoader
{
    /**
     * Set the locales that should be loaded.
     * By default it will load all locales in the translation files.
     *
     * @param array|null $locales
     *
     * @return \CodeZero\Translator\FileLoader\FileLoader
     */
    public function onlyLocales($locales);

    /**
     * Load empty translations.
     *
     * @param bool $empty
     *
     * @return \CodeZero\Translator\FileLoader\FileLoader
     */
    public function includeEmpty($empty = true);

    /**
     * Load translations.
     *
     * @param string|null $langPath
     *
     * @return array
     * @throws \CodeZero\Translator\Exceptions\PathDoesNotExist
     */
    public function load($langPath = null);
}
