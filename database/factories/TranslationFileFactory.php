<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\CodeZero\Translator\Models\TranslationFile::class, function (Faker\Generator $faker) {
    return [
        'filename' => $faker->toLower($faker->word),
    ];
});
