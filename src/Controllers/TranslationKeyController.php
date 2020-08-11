<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\Models\TranslationKey;
use CodeZero\Translator\Models\TranslationFile;
use Illuminate\Http\Request;

class TranslationKeyController extends Controller
{
    /**
     * List all translation keys of the given translation file.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(TranslationFile $file)
    {
        $keys = $file->getTranslationKeys();

        return response()->json($keys);
    }

    /**
     * Store a new translation key of the given translation file.
     *
     * @param \Illuminate\Http\Request $request
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, TranslationFile $file)
    {
        $rules = [
            'is_html' => ['boolean'],
            'key' => [
                'required',
                "unique_translation_key:{$file->isJson()},{$file->id}",
            ],
            'translations' => 'array',
            'translations.*' => ['nullable', 'string'],
        ];

        if ( ! $file->isJson()) {
            // No starting or ending dot, unless it's a JSON file.
            $rules['key'][] = 'regex:/^(?!\.).+(?<!\.)$/';
        }

        $attributes = $request->validate($rules);

        $key = $file->keys()->create($attributes);

        return response()->json($key);
    }

    /**
     * Update the given translation key.
     *
     * @param \Illuminate\Http\Request $request
     * @param \CodeZero\Translator\Models\TranslationKey $key
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TranslationKey $key)
    {
        $file = $key->translationFile;

        $rules = [
            'is_html' => ['boolean'],
            'key' => [
                'filled',
                "unique_translation_key:{$file->isJson()},{$file->id},{$key->id}",
            ],
            'translations' => ['array'],
            'translations.*' => ['nullable', 'string'],
        ];

        if ( ! $file->isJson()) {
            // No starting or ending dot, unless it's a JSON file.
            $rules['key'][] = 'regex:/^(?!\.).+(?<!\.)$/';
        }

        $attributes = $request->validate($rules);

        $key->update($attributes);

        return response()->json($key);
    }

    /**
     * Delete the given translation key.
     *
     * @param \CodeZero\Translator\Models\TranslationKey $key
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TranslationKey $key)
    {
        $key->delete();

        return response()->json($key);
    }
}
