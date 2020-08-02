<?php

namespace CodeZero\Translator\FileLoader;

interface FileLoader
{
    /**
     * Load empty translations.
     *
     * @param bool $load
     *
     * @return \CodeZero\Translator\FileLoader\FileLoader
     */
    public function loadEmpty($load = true);

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
