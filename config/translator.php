<?php

return [

    //'locales' => ['en', 'nl', 'fr'],
    'locales' => null,

    'import' => [
        'path' => resource_path('lang'),
    ],

    'export' => [
        'path' => storage_path('translator/lang'),
    ],

    'route' => [
        'prefix' => 'translator',
        'middleware' => ['web', 'auth'],
    ],

];
