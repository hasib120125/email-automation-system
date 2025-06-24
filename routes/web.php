<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/import-djs', [ImportController::class, 'showImportForm'])->name('import.djs');
Route::post('/import-djs', [ImportController::class, 'importCsv'])->name('import.djs.process');
