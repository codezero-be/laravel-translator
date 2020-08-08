<?php

namespace CodeZero\Translator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TranslationKey extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_html' => 'boolean',
        'translations' => 'array',
    ];

    /**
     * The model's default attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_html' => false,
        'translations' => '{}',
    ];

    /**
     * Check if this key has HTML translations.
     *
     * @return bool
     */
    public function isHtml()
    {
        return $this->is_html;
    }

    /**
     * The TranslationKeys of this TranslationFile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function translationFile()
    {
        return $this->belongsTo(TranslationFile::class, 'file_id');
    }

    /**
     * Get translations.
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get a translation in the given locale.
     *
     * @param string $locale
     *
     * @return string|null
     */
    public function getTranslation($locale)
    {
        return $this->translations[$locale] ?? null;
    }

    /**
     * Add a translation in the specified locale.
     *
     * @param string $locale
     * @param string $translation
     *
     * @return \CodeZero\Translator\Models\TranslationKey
     */
    public function addTranslation($locale, $translation)
    {
        $translations = $this->translations;
        $translations[$locale] = $translation;
        $this->translations = $translations;

        return $this;
    }

    /**
     * Scope a query to include translation keys that...
     * - match the namespace of the given key, with the given translation file ID,
     * - excluding the translation key with the given ignore ID.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $key
     * @param bool $isJson
     * @param int $fileId
     * @param int $ignoreId
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeUsingNamespace($query, $key, $isJson, $fileId = 0, $ignoreId = 0)
    {
        if ($fileId > 0) {
            $query = $query->where('file_id', $fileId);
        }

        if ($ignoreId > 0) {
            $query = $query->where('id', '!=', $ignoreId);
        }

        if ($isJson) {
            return $query->where('key', '=', $key);
        }

        $keys = $this->listKeyNamespaces($key);

        $query = $query->where(function ($query) use ($keys, $key) {
            $query->whereIn('key', $keys)->orWhere('key', 'LIKE', $key.'.%');
        });

        return $query;
    }

    /**
     * List the key and all of its parent namespaces.
     * If $key is 'root.sub1.sub2.key' you will get:
     * [
     *     'root',
     *     'root.sub1',
     *     'root.sub1.sub2',
     *     'root.sub1.sub2.key',
     * ]
     *
     * @param string $key
     *
     * @return array
     */
    protected function listKeyNamespaces($key)
    {
        $keyParts = explode('.', $key);

        $namespaces = Collection::make($keyParts)->reduce(function ($namespaces, $keyPart) {
            return $namespaces->push(
                trim("{$namespaces->last()}.{$keyPart}", '.')
            );
        }, Collection::make());

        return $namespaces->toArray();
    }

    /**
     * Get an attribute or look for a translation if the attribute is null.
     * This will allow you to access "$this->en", etc.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->getTranslation($key) ?: parent::getAttribute($key);
    }
}
