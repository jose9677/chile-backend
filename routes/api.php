<?php

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

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/registerEmployee', [App\Http\Controllers\AuthController::class, 'registerEmployee']);
Route::post('/registerNeighbor', [App\Http\Controllers\AuthController::class, 'registerNeighbor']);

Route::middleware('auth:api')->group(function(){
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
});
