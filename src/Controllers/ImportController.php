<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\Importer;

class ImportController extends Controller
{
    /**
     * Import language files from the filesystem to the database.
     *
     * @param \CodeZero\Translator\Importer $importer
     *
     * @return \Illuminate\Support\Collection
     */
    public function store(Importer $importer)
    {
        return $importer->import();
    }

    /**
     * Sync the language files with the database translations.
     *
     * @param \CodeZero\Translator\Importer $importer
     *
     * @return \Illuminate\Support\Collection
     */
    public function update(Importer $importer)
    {
        $databaseWins = !! request('database_wins');

        return $databaseWins
            ? $importer->databaseWins()->sync()
            : $importer->sync();
    }
}
