<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ExcelController;
use App\Http\Controllers\HourController;

Route::post('/upload-excel', [ExcelController::class, 'import']);

Route::get('/hours', [HourController::class, 'index']);

Route::post('/hours', [HourController::class, 'storeOrUpdate']);


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World']);

});
