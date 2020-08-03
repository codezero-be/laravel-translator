<?php

namespace CodeZero\Translator\FileLoader;

interface FileLoader
{
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
