<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\CodeZero\Translator\Models\TranslationFile::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->toLower($faker->word),
    ];
});
