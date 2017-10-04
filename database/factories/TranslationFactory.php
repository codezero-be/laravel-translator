<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\CodeZero\Translator\Models\Translation::class, function (Faker\Generator $faker) {
    return [
        'file_id' => function () {
            return factory(\CodeZero\Translator\Models\TranslationFile::class)->create()->id;
        },
        'key' => implode('.', [
            $faker->randomElement(['first', 'second', 'third']),
            $faker->toLower($faker->word),
            $faker->toLower($faker->word),
        ])
    ];
});
