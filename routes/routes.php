<?php

use CodeZero\Translator\Controllers\ExportController;
use CodeZero\Translator\Controllers\ImportController;
use CodeZero\Translator\Controllers\KeepAliveController;
use CodeZero\Translator\Controllers\TranslationFileController;
use CodeZero\Translator\Controllers\TranslationKeyController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'translator.',
    'prefix' => Config::get('translator.route.prefix'),
    'middleware' => Config::get('translator.route.middleware'),
], function () {

    Route::get(   'keep-alive', ['as' => 'keep.alive', 'uses' => KeepAliveController::class.'@index']);
    Route::post(  'import',     ['as' => 'import',     'uses' => ImportController::class.'@store']);
    Route::post(  'export',     ['as' => 'export',     'uses' => ExportController::class.'@store']);

    Route::get(   'files',        ['as' => 'files.index',   'uses' => TranslationFileController::class.'@index']);
    Route::post(  'files',        ['as' => 'files.store',   'uses' => TranslationFileController::class.'@store']);
    Route::patch( 'files/{file}', ['as' => 'files.update',  'uses' => TranslationFileController::class.'@update']);
    Route::delete('files/{file}', ['as' => 'files.destroy', 'uses' => TranslationFileController::class.'@destroy']);

    Route::get(   'files/{file}/keys', ['as' => 'keys.index',   'uses' => TranslationKeyController::class.'@index']);
    Route::post(  'files/{file}/keys', ['as' => 'keys.store',   'uses' => TranslationKeyController::class.'@store']);
    Route::patch( 'keys/{key}',        ['as' => 'keys.update',  'uses' => TranslationKeyController::class.'@update']);
    Route::delete('keys/{key}',        ['as' => 'keys.destroy', 'uses' => TranslationKeyController::class.'@destroy']);

});
