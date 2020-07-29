<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use CodeZero\Translator\Models\TranslationFile;
use CodeZero\Translator\Models\TranslationKey;

$factory->define(TranslationKey::class, function (Faker\Generator $faker) {
    return [
        'file_id' => function () {
            return factory(TranslationFile::class)->create()->id;
        },
        'key' => $faker->toLower($faker->word),
    ];
});
