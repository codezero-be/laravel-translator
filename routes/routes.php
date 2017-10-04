<?php

use CodeZero\Translator\Controllers\ExportController;
use CodeZero\Translator\Controllers\ImportController;
use CodeZero\Translator\Controllers\KeepAliveController;
use CodeZero\Translator\Controllers\TranslationController;
use CodeZero\Translator\Controllers\TranslationFileController;

Route::group(['prefix' => config('translator.route.prefix'), 'as' => 'translator.', 'middleware' => config('translator.route.middleware')], function () {

    Route::get(   'keep-alive', ['as' => 'keep.alive', 'uses' => KeepAliveController::class.'@index']);
    Route::post(  'import',     ['as' => 'import',     'uses' => ImportController::class.'@store']);
    Route::patch( 'sync',       ['as' => 'sync',       'uses' => ImportController::class.'@update']);
    Route::post(  'export',     ['as' => 'export',     'uses' => ExportController::class.'@store']);

    Route::get(   'files',        ['as' => 'files',         'uses' => TranslationFileController::class.'@index']);
    Route::post(  'files',        ['as' => 'files.store',   'uses' => TranslationFileController::class.'@store']);
    Route::patch( 'files/{file}', ['as' => 'files.update',  'uses' => TranslationFileController::class.'@update']);
    Route::delete('files/{file}', ['as' => 'files.destroy', 'uses' => TranslationFileController::class.'@destroy']);

    Route::get(   'files/{file}',               ['as' => 'translations',         'uses' => TranslationController::class.'@index']);
    Route::post(  'files/{file}',               ['as' => 'translations.store',   'uses' => TranslationController::class.'@store']);
    Route::patch( 'translations/{translation}', ['as' => 'translations.update',  'uses' => TranslationController::class.'@update']);
    Route::delete('translations/{translation}', ['as' => 'translations.destroy', 'uses' => TranslationController::class.'@destroy']);

});
