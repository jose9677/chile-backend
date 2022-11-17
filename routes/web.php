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

//USERCONTROLLER
Route::get('/details-user', [App\Http\Controllers\usersController::class, 'detailsUser']);
Route::get('/search-user/{rut}', [App\Http\Controllers\usersController::class, 'search']);
Route::put('/update-user/{rut}', [App\Http\Controllers\usersController::class, 'update']);
Route::delete('/delete-user/{rut}', [App\Http\Controllers\usersController::class, 'delete']);
Route::put('/change-password', [App\Http\Controllers\usersController::class, 'changePassword']);

//AUTHCONTROLLER
Route::post('/validate-email', [App\Http\Controllers\AuthController::class, 'validateEmail']);

//REQUESTCONTROLLER
Route::post('/create-request', [App\Http\Controllers\requestController::class, 'createRequest']);

// USER_REQUESTCONTROLLER
Route::post('/add-request', [App\Http\Controllers\user_requestController::class, 'add_request']);

//DOCUMENTS

Route::get('/form', [App\Http\Controllers\documentsController::class, 'index']);
Route::post('/save', [App\Http\Controllers\documentsController::class, 'save']);