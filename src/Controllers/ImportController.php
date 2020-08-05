<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\FileLoader\FileLoader;
use CodeZero\Translator\Importer\Importer;
use CodeZero\Translator\Models\TranslationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ImportController extends Controller
{
    /**
     * Import translation files from the filesystem to the database.
     *
     * @param \Illuminate\Http\Request $request
     * @param \CodeZero\Translator\FileLoader\FileLoader $loader
     * @param \CodeZero\Translator\Importer\Importer $importer
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \CodeZero\Translator\Exceptions\PathDoesNotExist
     */
    public function store(Request $request, FileLoader $loader, Importer $importer)
    {
        $request->validate([
            'replace_existing' => 'nullable|bool',
            'fill_missing' => 'nullable|bool',
            'include_empty' => 'nullable|bool',
        ]);

        $fillMissing = $request->get('fill_missing', false);
        $replaceExisting = $request->get('replace_existing', false);
        $includeEmpty = $request->get('include_empty', false);

        $locales = Config::get('translator.locales');
        $langPath = Config::get('translator.import.path');

        $loadedFiles = $loader
            ->includeEmpty($includeEmpty)
            ->onlyLocales($locales)
            ->load($langPath);

        $importer
            ->fillMissing($fillMissing)
            ->replaceExisting($replaceExisting)
            ->includeEmpty($includeEmpty)
            ->onlyLocales($locales)
            ->import($loadedFiles);

        $translationFiles = TranslationFile::with('translationKeys')->get();

        return response()->json($translationFiles);
    }
}
