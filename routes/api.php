<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CongéController;
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
});

// Statistiques
Route::group(['prefix' => 'stat'], function () {
    Route::get('count-employees-rh', [StatistiqueController::class, 'CountEmployeeRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('count-employees-admin', [StatistiqueController::class, 'CountEmployeeAdmin'])->middleware(['auth:api', 'role:admin']);
    Route::get('count-employees-ResponsableRh', [StatistiqueController::class, 'CountEmployeeResponsableRH'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('employees-present-today', [StatistiqueController::class, 'EmployeesPresentToday'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-present-today-rh', [StatistiqueController::class, 'EmployeesPresentTodayRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('employees-present-list', [StatistiqueController::class, 'EmployeesPresentList'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-present-list-rh', [StatistiqueController::class, 'EmployeesPresentListRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('employees-absent-today', [StatistiqueController::class, 'EmployeesAbsentToday'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-absent-today-rh', [StatistiqueController::class, 'EmployeesAbsentTodayRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('employees-absent-list', [StatistiqueController::class, 'EmployeesAbsentTodayList'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-absent-list-rh', [StatistiqueController::class, 'EmployeesAbsentTodayListRH'])->middleware(['auth:api', 'role:RH']);
    Route::get('top-present-rh', [StatistiqueController::class, 'EmployeLePlusPresentMoisDernierAdmin'])->middleware(['auth:api', 'role:RH']);
    Route::get('top-present-admin', [StatistiqueController::class, 'EmployeLePlusPresentMoisDernierRH'])->middleware(['auth:api', 'role:admin']);
});

// Tâches
Route::group(['prefix' => 'taches'], function () {
    Route::get('get_taches', [TacheController::class, 'index'])->middleware(['auth:api', 'role:chefProjet']);
    Route::get('get_tache/{id}', [TacheController::class, 'getDataById'])->middleware(['auth:api', 'role:chefProjet']);
    Route::post('add_tache', [TacheController::class, 'store'])->middleware(['auth:api', 'role:chefProjet']);
    Route::put('update_tache/{id}', [TacheController::class, 'update'])->middleware(['auth:api', 'role:chefProjet,employee']);
    Route::get('employees/{id}', [TacheController::class, 'getEmployeeBydepartement'])->middleware(['auth:api', 'role:chefProjet']);
    Route::get('getTacheByUser/{user_id}', [TacheController::class, 'getTacheByUserId'])->middleware(['auth:api', 'role:employee']);
});

// Notifications
Route::group(['prefix' => 'Notification'], function () {
    Route::get('{user_id}', [NotificationController::class, 'getNotification'])->middleware(['auth:api', 'role:chefProjet,employee,ResponsableRh,agentRh']);
    Route::put('read/{id}', [NotificationController::class, 'read'])->middleware(['auth:api', 'role:chefProjet,employee,ResponsableRh,agentRh']);
});

// Congés
Route::group(['prefix' => 'congé'], function () {
    // Demandes
    Route::post('add', [CongéController::class, 'demandeConge'])->middleware(['auth:api', 'role:employee,chefProjet']);
    Route::post('addConge', [CongéController::class, 'demandeCongeAgentRH'])->middleware(['auth:api', 'role:agentRh']);

    // Pré-approbation/refus Agent RH
    Route::put('pre-approve/{id}', [CongéController::class, 'preApproveConge'])->middleware(['auth:api', 'role:agentRh']);
    Route::put('pre-refuse/{id}', [CongéController::class, 'preRefuseConge'])->middleware(['auth:api', 'role:agentRh']);

    // Approbation finale Responsable RH
    Route::put('final-approve/{id}', [CongéController::class, 'finalApproveConge'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::put('final-refuse/{id}', [CongéController::class, 'finalRefuseConge'])->middleware(['auth:api', 'role:ResponsableRh']);

    // Approbation/refus Agent RH par Responsable RH
    Route::put('approve-agentRh/{id}', [CongéController::class, 'ApproveCongeAgentRh'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::put('refuse-agentRh/{id}', [CongéController::class, 'RefuseCongeAgentRh'])->middleware(['auth:api', 'role:ResponsableRh']);

    // Récupération des résultats et listes
    Route::get('result/{user_id}', [CongéController::class, 'getResultByUser'])->middleware(['auth:api', 'role:employee,chefProjet,agentRh']);
    Route::get('attenteResponsableRH', [CongéController::class, 'getCongeAttenteResponsable'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('attenteAgentRh', [CongéController::class, 'getcongéAttenteAgentRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('approveResponsableRH', [CongéController::class, 'getCongeApproveResonsableRH'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('approveAgentRH', [CongéController::class, 'getCongeApproveAgentRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('refuseResponsableRH', [CongéController::class, 'getCongeRefuseResponsableRH'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('refuseAgentRH', [CongéController::class, 'getCongeRefuseAgentRH'])->middleware(['auth:api', 'role:agentRh']);
});
