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

Route::get('/dropdownInfo', [App\Http\Controllers\decriptionsController::class, 'dropdownInfo']);

//USERCONTROLLER
Route::get('/details-user', [App\Http\Controllers\usersController::class, 'detailsUser']);
Route::get('/search-user/{rut}', [App\Http\Controllers\usersController::class, 'search']);
Route::put('/update-user/{rut}', [App\Http\Controllers\usersController::class, 'update']);
Route::delete('/delete-user/{rut}', [App\Http\Controllers\usersController::class, 'delete']);
Route::put('/change-password', [App\Http\Controllers\usersController::class, 'changePassword']);
Route::post('/validate-user', [App\Http\Controllers\usersController::class, 'validateUser']);
Route::get('/employees', [App\Http\Controllers\usersController::class, 'employees']);
Route::post('/add-supervisor/{rut}', [App\Http\Controllers\usersController::class, 'add_supervisor']);
Route::get('/detailsAssistant/{rut}', [App\Http\Controllers\usersController::class, 'detailsAssistant']);
Route::get('/detailsSupervisor', [App\Http\Controllers\usersController::class, 'detailsSupersisor']);
Route::delete('/restartUser/{rut}', [App\Http\Controllers\usersController::class, 'restartUser']);

//AUTHCONTROLLER
Route::post('/validate-email', [App\Http\Controllers\AuthController::class, 'validateEmail']);
Route::put('/refresh-otp', [App\Http\Controllers\AuthController::class, 'refreshOtp']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/validateRut', [App\Http\Controllers\AuthController::class, 'validateRut']);
Route::post('/test', [App\Http\Controllers\AuthController::class, 'test']);

//SOLICITUDESCONTROLLER
Route::post('/create-request', [App\Http\Controllers\solicitudesController::class, 'createRequest']);
Route::get('/detail-request', [App\Http\Controllers\solicitudesController::class, 'detailRequest']);
Route::get('/get-request/{id}', [App\Http\Controllers\solicitudesController::class, 'getRequest']);
Route::put('/update-request/{id}', [App\Http\Controllers\solicitudesController::class, 'updateRequest']);
Route::delete('/delete-request/{id}', [App\Http\Controllers\solicitudesController::class, 'deleteRequest']);
Route::put('/confirmNeightbor/{id}', [App\Http\Controllers\solicitudesController::class, 'confirmNeightbor']);
Route::put('/programDate/{id}', [App\Http\Controllers\solicitudesController::class, 'programDate']);
Route::put('/reprogramDate/{id}', [App\Http\Controllers\solicitudesController::class, 'reprogramDate']);
Route::put('/programVisitDate/{id}', [App\Http\Controllers\solicitudesController::class, 'programVisitDate']);
Route::post('/add-visitor/{id}', [App\Http\Controllers\solicitudesController::class, 'add_visitor']);
Route::get('/detailsVisitor', [App\Http\Controllers\solicitudesController::class, 'detailsVisitor']);

Route::put('/approve-request/{id}', [App\Http\Controllers\solicitudesController::class, 'approveRequest']);
Route::put('/reject-request/{id}', [App\Http\Controllers\solicitudesController::class, 'rejectRequest']);

Route::get('/request-assigned', [App\Http\Controllers\solicitudesController::class, 'requestAssigned']);

// USER_REQUESTCONTROLLER
Route::post('/add-request', [App\Http\Controllers\user_requestController::class, 'add_request']);
Route::post('/add_employeeRut', [App\Http\Controllers\user_requestController::class, 'add_employeeRut']);
Route::get('/detail-neightbor/{rut_user}', [App\Http\Controllers\user_requestController::class, 'detailNeightborRequest']);
Route::get('/joinRequestUsers/{id}', [App\Http\Controllers\user_requestController::class, 'joinRequestUsers']);
Route::get('/joinRequestEmployee/{employee_rut}', [App\Http\Controllers\user_requestController::class, 'joinRequestEmployee']);

//ROLES
Route::get('/details-roles', [App\Http\Controllers\rolesController::class, 'detailsRoles']);

//DOCUMENTS
Route::get('/form', [App\Http\Controllers\documentsController::class, 'index']);
Route::post('/save', [App\Http\Controllers\documentsController::class, 'save']);

Route::post('/file/{id_document_type}', [App\Http\Controllers\documentsController::class, 'file']);