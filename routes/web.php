<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SviSimuleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/svi', [SviSimuleController::class, 'index'])->name('svi.index');
Route::get('/svi/{message}/play', [SviSimuleController::class, 'play'])->name('svi.play');
Route::post('/svi/ingest', [SviSimuleController::class, 'ingest'])->name('svi.ingest');