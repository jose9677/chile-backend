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
Route::post('/registerNeighbor', [App\Http\Controllers\AuthController::class, 'registerNeighbor']);

//WOTSCONTROLLER....
Route::post('/wots-registerEmployee', [App\Http\Controllers\wotsController::class, 'wotsRegisterEmployee']);
Route::post('/wots-registerNeighbor', [App\Http\Controllers\wotsController::class, 'wotsRegisterNeighbor']);
Route::post('/wots-profile-pic/{rut}', [App\Http\Controllers\wotsController::class, 'wotsProfilePic']);
Route::get('/wots-consult-email', [App\Http\Controllers\wotsController::class, 'wotsConsultEmail']);
Route::get('/wots-dropdownInfo', [App\Http\Controllers\wotsController::class, 'wotsDropdownInfo']);

Route::post('/wots-authJWT', [App\Http\Controllers\wotsController::class, 'wotsAuthJWT']);

Route::middleware('auth:api')->group(function(){
    
    //USERCONTROLLER...
    Route::get('/details-user', [App\Http\Controllers\usersController::class, 'detailsUser']);
    Route::get('/search-user/{rut}', [App\Http\Controllers\usersController::class, 'search']);
    Route::put('/update-user/{rut}', [App\Http\Controllers\usersController::class, 'update']);
    Route::put('/update-employee/{rut}', [App\Http\Controllers\usersController::class, 'updateEmployee']);
    Route::delete('/delete-user/{rut}', [App\Http\Controllers\usersController::class, 'delete']);
    Route::get('/employees', [App\Http\Controllers\usersController::class, 'employees']);
    Route::get('/assistant-list/{rut}', [App\Http\Controllers\usersController::class, 'assistantList']);
    Route::post('/add-supervisor/{rut}', [App\Http\Controllers\usersController::class, 'add_supervisor']);
    Route::get('/detailsAssistant/{rut}', [App\Http\Controllers\usersController::class, 'detailsAssistant']);
    Route::get('/details-assistants-and-supervisors', [App\Http\Controllers\usersController::class, 'detailsAssistant_Supervisor']);
    Route::get('/detailsSupervisor', [App\Http\Controllers\usersController::class, 'detailsSupersisor']);
    Route::put('/active-user/{rut}', [App\Http\Controllers\usersController::class, 'activeUser']);
    Route::put('/desactive-user/{rut}', [App\Http\Controllers\usersController::class, 'desactiveUser']);
    Route::get('/provider-user/{rut}', [App\Http\Controllers\usersController::class, 'providerUser']);
    Route::get('consult-email', [App\Http\Controllers\usersController::class, 'consultEmail']);

    //AUTHCONTROLLER...
    Route::put('/update-aps', [App\Http\Controllers\AuthController::class, 'updateAps']);
    Route::post('/registerEmployee', [App\Http\Controllers\AuthController::class, 'registerEmployee']);
    
    //SOLICITUDESCONTROLLER...
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
    Route::get('/detailsVisitor/{rut_employee}', [App\Http\Controllers\solicitudesController::class, 'detailsVisitor']);
    Route::put('/approve-request/{id}', [App\Http\Controllers\solicitudesController::class, 'approveRequest']);
    Route::put('/reject-request/{id}', [App\Http\Controllers\solicitudesController::class, 'rejectRequest']);
    Route::get('/request-assigned', [App\Http\Controllers\solicitudesController::class, 'requestAssigned']);
    Route::get('/guard-card/{id_simulation}', [App\Http\Controllers\solicitudesController::class, 'guardCard']);

    // USER_REQUESTCONTROLLER...
    Route::post('/add-request', [App\Http\Controllers\user_requestController::class, 'add_request']);
    Route::post('/add_employeeRut', [App\Http\Controllers\user_requestController::class, 'add_employeeRut']);
    Route::get('/detail-neightbor/{rut_user}', [App\Http\Controllers\user_requestController::class, 'detailNeightborRequest']);
    Route::get('/joinRequestUsers/{id}', [App\Http\Controllers\user_requestController::class, 'joinRequestUsers']);
    Route::get('/joinRequestEmployee/{employee_rut}', [App\Http\Controllers\user_requestController::class, 'joinRequestEmployee']);

    //ROLES...
    Route::get('/details-roles', [App\Http\Controllers\rolesController::class, 'detailsRoles']);
    Route::get('/details-roles-active', [App\Http\Controllers\rolesController::class, 'detailsActiveRoles']);
    Route::put('/active-rol/{id}', [App\Http\Controllers\rolesController::class, 'activeRol']);
    Route::put('/desactive-rol/{id}', [App\Http\Controllers\rolesController::class, 'desactiveRol']);

    //SIMULATIONS_CONTROLLER
    Route::get('/details-simulations', [App\Http\Controllers\simulationsController::class, 'detailsSimulation']);
    Route::get('/search-simulations/{id}', [App\Http\Controllers\simulationsController::class, 'searchSimulation']);
    Route::get('/users-simulations/{rut}', [App\Http\Controllers\simulationsController::class, 'Users_Simulations']);
    Route::post('/add-simulation/{rut}', [App\Http\Controllers\simulationsController::class, 'addSimulation']);
    Route::put('/update-simulation/{id}', [App\Http\Controllers\simulationsController::class, 'updateSimulation']);
    Route::delete('/delete-simulation/{id}', [App\Http\Controllers\simulationsController::class, 'deleteSimulation']);
    Route::put('/generate-simulation/{id}', [App\Http\Controllers\simulationsController::class, 'generateSimulation']);
    Route::put('/cancel-simulation/{id}/{id_motive}', [App\Http\Controllers\simulationsController::class, 'cancelSimulation']);

    //SIMULATIONS_SERVICES_CONTROLLER
    Route::get('/details-simulations-services', [App\Http\Controllers\simulations_servicesController::class, 'details_simulationService']);
    Route::post('/add-simulations-services', [App\Http\Controllers\simulations_servicesController::class, 'add_simulationService']);
    Route::post('/create-simulations-services', [App\Http\Controllers\simulations_servicesController::class, 'create_simulationService']);

    //SERVICES_CONTROLLER
    Route::get('/details-services', [App\Http\Controllers\servicesController::class, 'detailsServices']);
    Route::get('/search-services/{id}', [App\Http\Controllers\servicesController::class, 'searchServices']);
    Route::post('/add-services', [App\Http\Controllers\servicesController::class, 'add_services']);
    Route::put('/update-services/{id}', [App\Http\Controllers\servicesController::class, 'updateServices']);
    Route::delete('/delete-services/{id}', [App\Http\Controllers\servicesController::class, 'deleteServices']);

    //COPAGO
    Route::get('/details-copago/{id}', [App\Http\Controllers\simulationsController::class, 'detailsCopago']);
    Route::get('/details-certificate/{rut}', [App\Http\Controllers\simulationsController::class, 'detailsCerticate']);
    Route::put('/calc-copago/{id_simulation}', [App\Http\Controllers\simulationsController::class, 'calcCopago']);

    //DESCIPTIONCONTROLLER
    Route::get('/services-categories', [App\Http\Controllers\decriptionsController::class, 'Categories_Services']);

    //WOTSCONTROLLER....
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
});
