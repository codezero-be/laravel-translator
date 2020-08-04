<?php

return [

    //'locales' => ['en', 'nl', 'fr'],
    'locales' => null,

    'import' => [
        'path' => resource_path('lang'),
    ],

    'route' => [
        'prefix' => 'translator',
        'middleware' => ['web', 'auth'],
    ],

];
