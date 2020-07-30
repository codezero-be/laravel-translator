<?php

namespace CodeZero\Translator\FileLoader;

interface FileLoader
{
    /**
     * Skip empty translations.
     *
     * @return \CodeZero\Translator\FileLoader\FileLoader
     */
    public function skipEmpty();

    /**
     * Don't skip empty translations.
     *
     * @return \CodeZero\Translator\FileLoader\FileLoader
     */
    public function includeEmpty();
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
