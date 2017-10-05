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
            $file->translations->each->delete();
        });
    }

    /**
     * The Translations in this TranslationFile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(Translation::class, 'file_id');
    }
}
