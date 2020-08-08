<?php

namespace CodeZero\Translator\Validators;

use CodeZero\Translator\Models\TranslationKey;

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
        $isJson = $parameters[0] ?? null;
        $fileId = $parameters[1] ?? null;
        $ignoreId = $parameters[2] ?? null;

        return ! TranslationKey::usingNamespace($key, $isJson, $fileId, $ignoreId)->exists();
    }
}
