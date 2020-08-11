<?php

namespace CodeZero\Translator\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationFile extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            $file->getTranslationKeys()->each->delete();
        });
    }

    /**
     * Check if this is a JSON translation file.
     *
     * @return bool
     */
    public function isJson()
    {
        return $this->filename === '_json';
    }

    /**
     * The TranslationKeys of this TranslationFile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keys()
    {
        return $this->hasMany(TranslationKey::class, 'file_id');
    }

    /**
     * Get all related TranslationKeys.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTranslationKeys()
    {
        return $this->keys;
    }

    /**
     * Get a TranslationKey.
     *
     * @param string $key
     *
     * @return \CodeZero\Translator\Models\TranslationKey|null
     */
    public function getTranslationKey($key)
    {
        return $this->getTranslationKeys()->where('key', $key)->first();
    }
}
