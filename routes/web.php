<?php
use Illuminate\Support\Facades\Route;


Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
Route::post('/getDataByUrl', [App\Http\Controllers\HomeController::class, 'getDataByUrl'])->name('getDataByUrl');
Route::post('/getUrlsByProduct', [App\Http\Controllers\HomeController::class, 'getUrlsByProduct'])->name('getUrlsByProduct');
Route::get('/clearCache', [App\Http\Controllers\HomeController::class, 'clearCache'])->name('clearCache');

