<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CongéController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\TacheController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
// Auth
Route::group(['prefix' => 'auth'], function () {
    Route::put('restarpasword/{email}', [AuthController::class, 'restarpassword']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['auth:api', 'role:admin,employee,ResponsableRh,chefProjet,agentRh']);
    Route::put('updatepassword/{id}', [AuthController::class, 'updatepassword']);
    Route::get('User/{id}', [AuthController::class, 'getUserById'])->middleware(['auth:api', 'role:admin,employee,ResponsableRh,chefProjet,agentRh']);
    Route::put('verifMail/{id}', [AuthController::class, 'verifMail']);
    Route::put('updateUser/{id}', [AuthController::class, 'UpdateUser'])->middleware(['auth:api', 'role:admin,employee,ResponsableRh,chefProjet,agentRh']);
    Route::put('updatepassword1/{id}', [AuthController::class, 'updatePassword1'])->middleware(['auth:api', 'role:admin,employee,ResponsableRh,chefProjet,agentRh']);
    Route::get('employees', [AuthController::class, 'indexAgenRh'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('indexAdmin', [AuthController::class, 'indexAdmin'])->middleware(['auth:api', 'role:admin']);
    Route::get('getDataResponsableRH', [AuthController::class, 'getDataResponsableRH'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::post('/ouvrir-session', [AuthController::class, 'ouvrirSession'])->middleware(['auth:api', 'role:employee,agentRh,chefProjet']);
    Route::post('/fermer-session', [AuthController::class, 'fermerSession'])->middleware(['auth:api', 'role:employee,agentRh,chefProjet']);
    Route::get('/pause-dejeuner', [AuthController::class, 'pauseDejeuner'])->middleware(['auth:api', 'role:employee,agentRh,chefProjet']);
    Route::get('statUser', [AuthController::class, 'statUser'])->middleware(['auth:api', 'role:admin,RH']);
    Route::put('activeAccount/{id}', [AuthController::class, 'activeAccount'])->middleware(['auth:api', 'role:admin,ResponsableRh,agentRh']);
    Route::put('AccounNotActive/{id}', [AuthController::class, 'AccounNotActive'])->middleware(['auth:api', 'role:admin,ResponsableRh,agentRh']);
    Route::get('session-status', [AuthController::class, 'getSessionStatus'])->middleware(['auth:api', 'role:employee,agentRh,chefProjet']);
    // Route::get('searchUsers', [AuthController::class, 'searchUser'])->middleware(['auth:api', 'role:RH,admin']);
});
// // departements
// Route::group(['prefix' => 'departements'], function () {
//     Route::get('get_departements', [DepartementController::class, 'index'])->middleware(['auth:api', 'role:admin,RH']);
//     Route::get('get_departements/{id}', [DepartementController::class, 'getdepartementsById'])->middleware(['auth:api', 'role:admin']);
//     Route::post('add_departement', [DepartementController::class, 'store'])->middleware(['auth:api', 'role:admin']);
//     Route::put('update_departement/{id}', [DepartementController::class, 'update'])->middleware(['auth:api', 'role:admin']);
//     Route::delete('delete_departement/{id}', [DepartementController::class, 'destroy'])->middleware(['auth:api', 'role:admin']);
//     Route::get('searchDepartement/search', [DepartementController::class, 'search'])->middleware(['auth:api', 'role:admin']);
// });
// absence
// Route::group(['prefix' => 'absence'],  function () {
//     // Route::post('verifier-absences', [AuthController::class, 'Absence'])->middleware('role:admin,RH');
//      Route::get('absence/{id}', [AuthController::class, 'absenceEmployee'])->middleware('role:admin,RH,employee');
//     // Route::post('reset-salaire', [AuthController::class, 'resetSalaireMensuel'])->middleware('role:admin');
//     // Route::get('most-absent', [StatistiqueController::class, 'EmployeePlusAbsent'])->middleware('role:admin,RH');
//     // Route::get('most-present', [StatistiqueController::class, 'EmployeePlusPresent'])->middleware('role:admin,RH');
// });
Route::group(['prefix' => 'stat'], function () {
    Route::get('count-employees-rh', [StatistiqueController::class, 'CountEmployeeRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('count-employees-admin', [StatistiqueController::class, 'CountEmployeeAdmin'])->middleware(['auth:api', 'role:admin']);
    Route::get('count-employees-ResponsableRh', [StatistiqueController::class, 'CountEmployeeResponsableRH'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('employees-present-today-rh', [StatistiqueController::class, 'EmployeesPresentTodayRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('employees-present-today', [StatistiqueController::class, 'EmployeesPresentToday'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-present-list-rh', [StatistiqueController::class, 'EmployeesPresentListRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('employees-present-list', [StatistiqueController::class, 'EmployeesPresentList'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-absent-today-rh', [StatistiqueController::class, 'EmployeesAbsentTodayRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('employees-absent-today', [StatistiqueController::class, 'EmployeesAbsentToday'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-absent-list-rh', [StatistiqueController::class, 'EmployeesAbsentTodayListRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('employees-absent-list', [StatistiqueController::class, 'EmployeesAbsentTodayList'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('top-present-rh', [StatistiqueController::class, 'EmployeLePlusPresentMoisDernierAdmin'])->middleware(['auth:api', 'role:RH']);
    Route::get('top-present-admin', [StatistiqueController::class, 'EmployeLePlusPresentMoisDernierRH'])->middleware(['auth:api', 'role:admin']);
});
Route::group(['prefix' => 'taches'], function () {
    Route::get('get_taches', [TacheController::class, 'index'])->middleware(['auth:api', 'role:chefProjet']);
    Route::get('get_tache/{id}', [TacheController::class, 'getDataById'])->middleware(['auth:api', 'role:chefProjet']);
    Route::post('add_tache', [TacheController::class, 'store'])->middleware(['auth:api', 'role:chefProjet']);
    Route::put('update_tache/{id}', [TacheController::class, 'update'])->middleware(['auth:api', 'role:chefProjet,employee']);
    // Route::delete('delete_tache/{id}', [TacheController::class, 'destroy']);
    // Route::get('searchTache/search', [TacheController::class, 'search']);
    Route::get('employees/{id}', [TacheController::class, 'getEmployeeBydepartement'])->middleware(['auth:api', 'role:chefProjet']);
    Route::get('getTacheByUser/{user_id}', [TacheController::class, 'getTacheByUserId'])->middleware(['auth:api', 'role:employee']);
});
Route::group(['prefix' => 'Notification'], function () {
    Route::get('{user_id}', [NotificationController::class, 'getNotification'])->middleware(['auth:api', 'role:chefProjet,employee,RH']);
    Route::put('read/{id}', [NotificationController::class, '   '])->middleware(['auth:api', 'role:chefProjet,employee,RH']);
});

Route::group(['prefix' => 'congé'], function () {

    Route::post('add', [CongéController::class, 'demandeConge'])
        ->middleware(['auth:api', 'role:employee,chefProjet']);

    Route::post('addConge', [CongéController::class, 'demandeCongeAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::put('pre-approve/{id}', [CongéController::class, 'preApproveConge'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::put('pre-refuse/{id}', [CongéController::class, 'preRefuseConge'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::put('final-approve/{id}', [CongéController::class, 'finalApproveConge'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::put('approve-agentRh/{id}', [CongéController::class, 'ApproveCongeAgentRh'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::put('refuse-agentRh/{id}', [CongéController::class, 'RefuseCongeAgentRh'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::put('final-refuse/{id}', [CongéController::class, 'finalRefuseConge'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::get('result/{user_id}', [CongéController::class, 'getResultByUser'])
        ->middleware(['auth:api', 'role:employee,chefProjet, agentRh']);
    Route::get('attente', [CongéController::class, 'getCongeAttente'])
        ->middleware(['auth:api', 'role:agentRh,ResponsableRh']);

    Route::get('approve', [CongéController::class, 'getCongeApprove'])
        ->middleware(['auth:api', 'role:agentRh,ResponsableRh']);

    Route::get('refuse', [CongéController::class, 'getCongeRefuse'])
        ->middleware(['auth:api', 'role:agentRh,ResponsableRh']);
});
