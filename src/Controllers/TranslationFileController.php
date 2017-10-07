<?php

namespace CodeZero\Translator\Controllers;

use CodeZero\Translator\Models\TranslationFile;
use Illuminate\Validation\Rule;

class TranslationFileController extends Controller
{
    /**
     * List all TranslationFiles.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function index()
    {
        return TranslationFile::all();
    }

    /**
     * Store a new TranslationFile.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function store()
    {
        $this->validate(request(), [
            'name' => [
                'required',
                'regex:/^[a-zA-Z0-9\-]+$/',
                Rule::unique('translation_files', 'name')->where(function ($query) {
                    $query->where('package', '=', request('package'));
                }),
            ],
            'package' => [
                'nullable',
                'regex:/^[a-zA-Z0-9\-]+$/',
            ],
        ]);

        return TranslationFile::create(
            array_filter(request()->only(['name', 'package']))
        );
    }

    /**
     * Update the given TranslationFile.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update(TranslationFile $file)
    {
        $this->validate(request(), [
            'name' => [
                'required',
                'regex:/^[a-zA-Z0-9\-]+$/',
                Rule::unique('translation_files', 'name')->ignore($file->id)->where(function ($query) {
                    $query->where('package', '=', request('package'));
                }),
            ],
            'package' => [
                'nullable',
                'regex:/^[a-zA-Z0-9\-]+$/',
            ],
        ]);

        return tap($file)->update(
            request()->optional(['name', 'package'])
        );
    }

    /**
     * Delete the given TranslationFile.
     *
     * @param \CodeZero\Translator\Models\TranslationFile $file
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function destroy(TranslationFile $file)
    {
        return tap($file)->delete();
    }
}
