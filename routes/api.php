<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\SausageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/barcodes-insert', [SausageController::class, 'insertBarcodes']);
Route::post('/last-insert', [SausageController::class, 'lastInsert']);

//public Apis
Route::middleware(['token_check', 'throttle:60,1'])->group(function () {
    Route::post('/v1/fetch-slaughter-data', [ApiController::class, 'getSlaughterData']);
});
