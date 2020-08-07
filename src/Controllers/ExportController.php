<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\Exporter\Exporter;
use CodeZero\Translator\Models\TranslationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ExportController extends Controller
{
    /**
     * Export database translations to the filesystem.
     *
     * @param \Illuminate\Http\Request $request
     * @param \CodeZero\Translator\Exporter\Exporter $exporter
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Exporter $exporter)
    {
        $request->validate([
            'include_empty' => 'nullable|bool',
        ]);

        $includeEmpty = $request->get('include_empty', false);

        $locales = Config::get('translator.locales');
        $exportPath = Config::get('translator.export.path');
        $files = TranslationFile::with('translationKeys')->get();

        $exporter
            ->includeEmpty($includeEmpty)
            ->onlyLocales($locales)
            ->export($files, $exportPath);

        return response()->json();
    }
}
