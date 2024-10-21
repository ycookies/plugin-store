<?php

use Dcat\Admin\PluginStore\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('plugin-store/index', Controllers\PluginStoreController::class.'@index');
Route::post('plugin_store/install', Controllers\PluginStoreController::class.'@install');
Route::get('plugin-store/viewTagsRequire', Controllers\PluginStoreController::class.'@viewTagsRequire');
Route::get('plugin-store/package-version-install', Controllers\PluginStoreController::class.'@packageVersionInstall');
Route::get('plugin-store/viewproduct', Controllers\PluginStoreController::class.'@viewproduct');

Route::get('plugin-store/dev-helper', Controllers\PluginDevHelperController::class.'@index');
Route::get('plugin-store/setting', Controllers\PluginStoreConfigController::class.'@index');