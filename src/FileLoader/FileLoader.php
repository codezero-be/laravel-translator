<?php

namespace CodeZero\Translator\FileLoader;

interface FileLoader
{
    /**
     * Skip empty translations.
     *
     * @param bool $skip
     *
     * @return \CodeZero\Translator\FileLoader\FileLoader
     */
    public function skipEmpty($skip = true);

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
