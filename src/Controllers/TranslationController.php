<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\Models\Translation;
use CodeZero\Translator\Models\TranslationFile;

class TranslationController extends Controller
{
    /**
     * List all Translations in the given TranslationFile.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function index(TranslationFile $file)
    {
        return $file->translations;
    }

    /**
     * Store a new Translation in the given TranslationFile.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function store(TranslationFile $file)
    {
        $this->validate(request(), [
            'is_html' => 'boolean',
            'key' => [
                'required',
                'regex:/^(?!\.)[a-zA-Z0-9\-\.]+(?<!\.)$/',
                "unique_translation_key:{$file->id}",
            ]
        ]);

        return $file->translations()->create(
            request()->intersect(['is_html', 'key', 'body'])
        );
    }

    /**
     * Update the given Translation.
     *
     * @param \CodeZero\Translator\Models\Translation $translation
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update(Translation $translation)
    {
        $this->validate(request(), [
            'is_html' => 'boolean',
            'key' => [
                'filled',
                'regex:/^(?!\.)[a-zA-Z0-9\-\.]+(?<!\.)$/',
                "unique_translation_key:{$translation->file_id},{$translation->id}",
            ]
        ]);

        return tap($translation)->update(
            request()->optional(['is_html', 'key', 'body'])
        );
    }

    /**
     * Delete the given Translation.
     *
     * @param \CodeZero\Translator\Models\Translation $translation
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function destroy(Translation $translation)
    {
        return tap($translation)->delete();
    }
}
