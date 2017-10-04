<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\Exporter;

class ExportController extends Controller
{
    /**
     * Export database translations to the filesystem.
     *
     * @param \CodeZero\Translator\Exporter $exporter
     *
     * @return array
     */
    public function store(Exporter $exporter)
    {
        return $exporter->export();
    }
}
