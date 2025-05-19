<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ExcelController;

Route::post('/upload-excel', [ExcelController::class, 'import']);


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World']);

});
