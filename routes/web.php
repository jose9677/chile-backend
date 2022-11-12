<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/details-user', [App\Http\Controllers\usersController::class, 'detailsUser']);
Route::get('/search-user/{rut}', [App\Http\Controllers\usersController::class, 'search']);
Route::put('/update-user/{rut}', [App\Http\Controllers\usersController::class, 'update']);
Route::delete('/delete-user/{rut}', [App\Http\Controllers\usersController::class, 'delete']);

Route::post('/validate-email', [App\Http\Controllers\AuthController::class, 'validateEmail']);
