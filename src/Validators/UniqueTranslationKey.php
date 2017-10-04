<?php

namespace CodeZero\Translator\Validators;

use CodeZero\Translator\Models\Translation;

class UniqueTranslationKey
{
    /**
     * Check if the given translation key or its namespace
     * is already in use within the same translation file.
     * Exclude the translation with the given ignore ID from the results.
     *
     * @param string $attribute
     * @param string $key
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return bool
     */
    public function validate($attribute, $key, $parameters, $validator) {
        $fileId = $parameters[0] ?? null;
        $ignoreId = $parameters[1] ?? null;

        return ! Translation::usingNamespace($key, $fileId, $ignoreId)->exists();
    }
}
