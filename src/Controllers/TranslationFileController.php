<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\Models\TranslationFile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TranslationFileController extends Controller
{
    /**
     * List all translation files.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $files = TranslationFile::with('translationKeys')->get();

        return response()->json($files);
    }

    /**
     * Store a new translation file.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $regex = '/^[a-zA-Z0-9\-_]+$/';

        $attributes = $request->validate([
            'filename' => [
                'required',
                Rule::unique('translation_files', 'filename')->where(function ($query) use ($request) {
                    $query->where('vendor', '=', $request->get('vendor'));
                }),
                "regex:{$regex}",
            ],
            'vendor' => [
                'nullable',
                "regex:{$regex}",
            ],
        ]);

        $file = TranslationFile::create($attributes);

        return response()->json($file);
    }

    /**
     * Update the given translation file.
     *
     * @param \Illuminate\Http\Request $request
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TranslationFile $file)
    {
        $regex = '/^[a-zA-Z0-9\-_]+$/';

        $attributes = $request->validate([
            'filename' => [
                'required',
                Rule::unique('translation_files', 'filename')->where(function ($query) use ($request) {
                    $query->where('vendor', '=', $request->get('vendor'));
                })->ignore($file->id),
                "regex:{$regex}",
            ],
            'vendor' => [
                'present',
                'nullable',
                "regex:{$regex}",
            ],
        ]);

        $file->update($attributes);

        return response()->json($file);
    }

    /**
     * Delete the given translation file.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TranslationFile $file)
    {
        $file->delete();

        return response()->json($file);
    }
}
