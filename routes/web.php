<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;
use App\Http\Controllers\OauthController;
use Ixudra\Curl\Facades\Curl;

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

/*  //COPAGO
Route::get('/details-copago/{id}', [App\Http\Controllers\simulationsController::class, 'detailsCopago']);
Route::get('/details-certificate/{rut}', [App\Http\Controllers\simulationsController::class, 'detailsCerticate']);
Route::put('/calc-copago/{id_simulation}', [App\Http\Controllers\simulationsController::class, 'calcCopago']);

//DESCIPTIONCONTROLLER
Route::get('/services-categories', [App\Http\Controllers\decriptionsController::class, 'Categories_Services']);  */
Route::get('/dropdownInfo', [App\Http\Controllers\decriptionsController::class, 'dropdownInfo']);

//USERCONTROLLER
//Route::get('/details-user', [App\Http\Controllers\usersController::class, 'detailsUser']);
//Route::get('/search-user/{rut}', [App\Http\Controllers\usersController::class, 'search']);
//Route::put('/update-user/{rut}', [App\Http\Controllers\usersController::class, 'update']);
//Route::put('/update-employee/{rut}', [App\Http\Controllers\usersController::class, 'updateEmployee']);
//Route::delete('/delete-user/{rut}', [App\Http\Controllers\usersController::class, 'delete']);
Route::put('/change-password', [App\Http\Controllers\usersController::class, 'changePassword']);
Route::post('/validate-user', [App\Http\Controllers\usersController::class, 'validateUser']);
Route::delete('/restartUser/{rut}', [App\Http\Controllers\usersController::class, 'restartUser']);
/* Route::get('/employees', [App\Http\Controllers\usersController::class, 'employees']);
Route::get('/assistant-list/{rut}', [App\Http\Controllers\usersController::class, 'assistantList']);
Route::post('/add-supervisor/{rut}', [App\Http\Controllers\usersController::class, 'add_supervisor']);
Route::get('/detailsAssistant/{rut}', [App\Http\Controllers\usersController::class, 'detailsAssistant']);
Route::get('/details-assistants-and-supervisors', [App\Http\Controllers\usersController::class, 'detailsAssistant_Supervisor']);
Route::get('/detailsSupervisor', [App\Http\Controllers\usersController::class, 'detailsSupersisor']);
Route::put('/active-user/{rut}', [App\Http\Controllers\usersController::class, 'activeUser']);
Route::put('/desactive-user/{rut}', [App\Http\Controllers\usersController::class, 'desactiveUser']);
Route::get('/provider-user/{rut}', [App\Http\Controllers\usersController::class, 'providerUser']); 
Route::get('consult-email', [App\Http\Controllers\usersController::class, 'consultEmail']);*/

//AUTHCONTROLLER
Route::post('/validate-email', [App\Http\Controllers\AuthController::class, 'validateEmail']);
Route::put('/refresh-otp', [App\Http\Controllers\AuthController::class, 'refreshOtp']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/validateRut', [App\Http\Controllers\AuthController::class, 'validateRut']);
Route::post('/test', [App\Http\Controllers\AuthController::class, 'test']);
Route::post('/profile-pic/{rut}', [App\Http\Controllers\AuthController::class, 'profilePic']);
Route::put('/update-aps', [App\Http\Controllers\AuthController::class, 'updateAps']);

//MODIFY MODULE...
Route::get('/data-modifer/{rut}', [App\Http\Controllers\ModifyUserController::class, 'DataModifer']);
Route::get('/consult-modify/{id}', [App\Http\Controllers\ModifyDataController::class, 'allData']);

//MODIFIUSERCONTROLLER...
Route::get('/all-user-modify', [App\Http\Controllers\ModifyUserController::class, 'allMoifyUser']);
Route::get('/search-user-modify/{id_user}', [App\Http\Controllers\ModifyUserController::class, 'searchModifyUser']);
Route::post('/load-user-modify', [App\Http\Controllers\ModifyUserController::class, 'loadUserData']);

//MODIFIFYSOLICITUDECONTROLLER...
Route::get('/all-sol-modify', [App\Http\Controllers\ModifySolitudeController::class, 'allSolicitudeModify']);
Route::get('/search-sol-modify/{id_solicitude}', [App\Http\Controllers\ModifySolitudeController::class, 'searchSolicitude']);
Route::post('/load-sol-modify', [App\Http\Controllers\ModifySolitudeController::class, 'loadSolicitudeData']);

//MODIFYDOCUMENTS...
Route::post('/load-file-modify/{id}/{id_document_type}', [App\Http\Controllers\ModifyDocumentController::class, 'loadModifyFile']);

//SOLICITUDESCONTROLLER
/* Route::post('/create-request', [App\Http\Controllers\solicitudesController::class, 'createRequest']);
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
Route::get('/guard-card/{id_simulation}', [App\Http\Controllers\solicitudesController::class, 'guardCard']); */

// USER_REQUESTCONTROLLER
/* Route::post('/add-request', [App\Http\Controllers\user_requestController::class, 'add_request']);
Route::post('/add_employeeRut', [App\Http\Controllers\user_requestController::class, 'add_employeeRut']);
Route::get('/detail-neightbor/{rut_user}', [App\Http\Controllers\user_requestController::class, 'detailNeightborRequest']);
Route::get('/joinRequestUsers/{id}', [App\Http\Controllers\user_requestController::class, 'joinRequestUsers']);
Route::get('/joinRequestEmployee/{employee_rut}', [App\Http\Controllers\user_requestController::class, 'joinRequestEmployee']); */

/* //ROLES
Route::get('/details-roles', [App\Http\Controllers\rolesController::class, 'detailsRoles']);
Route::get('/details-roles-active', [App\Http\Controllers\rolesController::class, 'detailsActiveRoles']);
Route::put('/active-rol/{id}', [App\Http\Controllers\rolesController::class, 'activeRol']);
Route::put('/desactive-rol/{id}', [App\Http\Controllers\rolesController::class, 'desactiveRol']); */

//DOCUMENTS
Route::get('/form', [App\Http\Controllers\documentsController::class, 'index']);
Route::post('/save', [App\Http\Controllers\documentsController::class, 'save']);

Route::post('/file/{rut}/{id_document_type}', [App\Http\Controllers\documentsController::class, 'file']);

Route::get('/form-url', [App\Http\Controllers\documentsController::class, 'indexUrl']);
Route::post('/url-file/{url_doc}/{rut}/{id_document_type}', [App\Http\Controllers\documentsController::class, 'urlFile']);

/* //SIMULATIONS_CONTROLLER
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
Route::delete('/delete-services/{id}', [App\Http\Controllers\servicesController::class, 'deleteServices']); */

Route::post('/oauth/token', [OauthController::class, 'doGenerateToken'])->name('generate.token');
    Route::get('/oauth/token', [OauthController::class, 'doSuccessToken'])->name('token.success');
    Route::post('/oauth/renew-token', [OauthController::class, 'doRefreshAccessToken'])->name('generate.new-token');

    Route::post('/email/test', [MailController::class, 'doSendEmail'])->name('send.email');
    Route::get('/email/oauth', [OauthController::class, 'index']);

 //REPORTES EXCEL
Route::get('export-users', [App\Http\Controllers\exportController::class, 'export']);
Route::get('export-reports', [App\Http\Controllers\exportController::class, 'exportReport']);
Route::get('export-integramedica', [App\Http\Controllers\exportController::class, 'exportIntegramedica']);

//IMPORTAR REPORTES 
Route::get('aps', [App\Http\Controllers\apsController::class, 'index']);
Route::post('import-aps', [App\Http\Controllers\apsController::class, 'import']);
Route::get('search-update-aps/{id_aps}', [App\Http\Controllers\apsController::class, 'searchUpdateAps']);
Route::get('last-aps', [App\Http\Controllers\apsController::class, 'lastUpdateAps']);
Route::get('aps-historic', [App\Http\Controllers\apsController::class, 'apsHistoric']); 
Route::get('aps-download/{file}', [App\Http\Controllers\apsController::class, 'downloadFile']); 

//STATUS APS...
Route::get('details-aps-status', [App\Http\Controllers\apsController::class, 'detailsStatus']); 
Route::get('search-aps-status/{id_status}', [App\Http\Controllers\apsController::class, 'searchStatus']); 
Route::get('process-aps', [App\Http\Controllers\apsController::class, 'ProcessAps']); 

Route::get('token', [App\Http\Controllers\usersController::class, 'getNewToken']);
Route::post('wots-auth', [App\Http\Controllers\wotsController::class, 'wotsAuth']);
Route::post('wots-out', [App\Http\Controllers\wotsController::class, 'wotsOut']);

Route::get('curl', function(){
    $response = Curl::to('https://jsonplaceholder.typicode.com/posts/7')
    ->get();

    dd($response);
});
