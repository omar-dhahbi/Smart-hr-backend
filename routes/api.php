<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvanceSalireController;
use App\Http\Controllers\CongéController;
use App\Http\Controllers\IaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\TacheController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'auth'], function () {
    Route::put('restarpasword/{email}', [AuthController::class, 'restarpassword']);
    Route::post('register', [AuthController::class, 'register'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
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
    Route::get('search', [AuthController::class, 'searchUser'])
        ->middleware(['auth:api', 'role:admin,ResponsableRh,agentRh']);
    Route::get('historique-pointage/{date}', [AuthController::class, 'historiquePointage'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('historique-pointage-user/{id}', [AuthController::class, 'historiquePointageByUser'])
        ->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('historique-pointageAgent/{date}', [AuthController::class, 'historiquePointageAgentRH'])->middleware(['auth:api', 'role:agentRh']);

});
Route::group(['prefix' => 'stat'], function () {
    Route::get('count-employees-rh', [StatistiqueController::class, 'CountEmployeeRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('count-employees-admin', [StatistiqueController::class, 'CountEmployeeAdmin'])->middleware(['auth:api', 'role:admin']);
    Route::get('count-employees-ResponsableRh', [StatistiqueController::class, 'CountEmployeeResponsableRH'])->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('employees-present-today', [StatistiqueController::class, 'EmployeesPresentToday'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-present-today-rh', [StatistiqueController::class, 'EmployeesPresentTodayRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('employees-present-list', [StatistiqueController::class, 'EmployeesPresentList'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-present-list-rh', [StatistiqueController::class, 'EmployeesPresentListRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('employees-absent-today', [StatistiqueController::class, 'EmployeesAbsentToday'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-absent-today-rh', [StatistiqueController::class, 'EmployeesAbsentTodayRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('employees-absent-list', [StatistiqueController::class, 'EmployeesAbsentTodayList'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employees-absent-list-rh', [StatistiqueController::class, 'EmployeesAbsentTodayListRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('genre', [StatistiqueController::class, 'statGenre'])->middleware(['auth:api', 'role:agentRh,ResponsableRh,admin']);
    Route::get('employeeConge', [StatistiqueController::class, 'getEmployeEncongeToday'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('employeeCongeAgent', [StatistiqueController::class, 'getEmployeEncongeTodayAgentRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('employeeCongeList', [StatistiqueController::class, 'getEmployesEnCongeToday']);
    Route::get('employeeCongeAgentList', [StatistiqueController::class, 'getEmployesEnCongeTodayAgentRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('retard', [StatistiqueController::class, 'getRetardTodayResponsableRH'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('retardList', [StatistiqueController::class, 'getRetardListTodayResponsableRH'])->middleware(['auth:api', 'role:admin,ResponsableRh']);
    Route::get('retardAgent', [StatistiqueController::class, 'getRetardTodayAgentRH'])->middleware(['auth:api', 'role:agentRh']);
    Route::get('retardAgentList', [StatistiqueController::class, 'getRetardListTodayAgentRH'])->middleware(['auth:api', 'role:agentRh']);
});
Route::group(['prefix' => 'taches'], function () {
    Route::get('get_taches', [TacheController::class, 'index'])->middleware(['auth:api', 'role:chefProjet']);
    Route::get('get_tache/{id}', [TacheController::class, 'getDataById'])->middleware(['auth:api', 'role:chefProjet']);
    Route::post('add_tache', [TacheController::class, 'store']);
    Route::put('update_status/{id}', [TacheController::class, 'updateStatus'])
        ->middleware(['auth:api', 'role:chefProjet,employee']);
    Route::get('getTacheByUser/{user_id}', [TacheController::class, 'getTacheByUserId'])->middleware(['auth:api', 'role:employee']);
    Route::get('getEmployeeNonCongé', [TacheController::class, 'getEmployeeNonCongé'])->middleware(['auth:api', 'role:chefProjet']);
    Route::get('getTacheByProjet/{projet_id}', [TacheController::class, 'getTacheByProjetId']);
    Route::get('employees/{id}', [TacheController::class, 'getEmployeByTache'])->middleware(['auth:api', 'role:chefProjet']);
});
Route::group(['prefix' => 'notifications'], function () {
    Route::get('{user_id}', [NotificationController::class, 'getNotification'])
        ->middleware(['auth:api', 'role:chefProjet,employee,ResponsableRh,agentRh']);

    Route::put('read/{id}', [NotificationController::class, 'markAsRead'])
        ->middleware(['auth:api', 'role:chefProjet,employee,ResponsableRh,agentRh']);
});
Route::group(['prefix' => 'conges'], function () {

    Route::post('demande', [CongéController::class, 'demandeConge'])
        ->middleware(['auth:api', 'role:employee,chefProjet']);

    Route::post('demande-agent', [CongéController::class, 'demandeCongeAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::put('pre-approve/{id}', [CongéController::class, 'preApproveConge'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::put('pre-refuse/{id}', [CongéController::class, 'preRefuseConge'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::put('final-approve/{id}', [CongéController::class, 'finalApproveConge'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::put('final-refuse/{id}', [CongéController::class, 'finalRefuseConge'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::get('result/{user_id}', [CongéController::class, 'getResultByUser'])
        ->middleware(['auth:api', 'role:employee,chefProjet']);
    Route::get('resultAgentRH/{user_id}', [CongéController::class, 'getResultByUserAgentRh'])
        ->middleware(['auth:api', 'role:agentRh']);
    Route::get('responsable/attente', [CongéController::class, 'getCongeAttenteResponsable'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::get('responsable/acceptes', [CongéController::class, 'getCongeApproveResonsableRH'])->middleware(['auth:api', 'role:ResponsableRh']);

    Route::get('responsable/refuses', [CongéController::class, 'getCongeRefuseResponsableRH'])
        ->middleware(['auth:api', 'role:ResponsableRh']);

    Route::get('agent/attente', [CongéController::class, 'getCongeAttenteAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::get('agent/acceptes', [CongéController::class, 'getCongeApproveAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);

    Route::get('agent/refuses', [CongéController::class, 'getCongeRefuseAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);
});
Route::group(['prefix' => 'projets'], function () {

    Route::get('get_projet', [ProjetController::class, 'index'])
        ->middleware(['auth:api', 'role:employee,chefProjet']);

    Route::get('get_projet/{id}', [ProjetController::class, 'getProjetById'])
        ->middleware(['auth:api', 'role:employee,chefProjet']);

    Route::post('add', [ProjetController::class, 'store'])
        ->middleware(['auth:api', 'role:chefProjet']);

    Route::put('update/{id}', [ProjetController::class, 'update'])
        ->middleware(['auth:api', 'role:chefProjet']);

    Route::delete('delete/{id}', [ProjetController::class, 'destroy'])
        ->middleware(['auth:api', 'role:chefProjet']);

    Route::get('search', [ProjetController::class, 'search'])
        ->middleware(['auth:api', 'role:employee,chefProjet']);

});
Route::group(['prefix' => 'salaire'], function () {

    Route::get('fiche-paie/{user_id}', [AuthController::class, 'getFcihePaiParUserid'])
        ->middleware(['auth:api', 'role:agentRh,employee,chefProjet']);
    Route::post('generer-fiche-paie/{user_id}', [AuthController::class, 'genererFichePaie'])
        ->middleware(['auth:api', 'role:agentRh,employee,chefProjet']);
});

Route::group(['prefix' => 'avance-salaire'], function () {
    Route::post('demande', [AvanceSalireController::class, 'demandeAvance'])->middleware(['auth:api', 'role:employee,chefProjet']);
    Route::post('demandeAgent', [AvanceSalireController::class, 'AvanceSalaireAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);
    Route::put('pre-approve/{id}', [AvanceSalireController::class, 'preApprove'])
        ->middleware(['auth:api', 'role:agentRh']);
    Route::put('pre-refuse/{id}', [AvanceSalireController::class, 'preRefuse'])
        ->middleware(['auth:api', 'role:agentRh']);
    Route::put('final-approve/{id}', [AvanceSalireController::class, 'finalApprove'])
        ->middleware(['auth:api', 'role:ResponsableRh']);
    Route::put('final-refuse/{id}', [AvanceSalireController::class, 'finalRefuse'])
        ->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('attenteResponsable', [AvanceSalireController::class, 'getAvanceAttenteResponsable'])
        ->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('acceptesResponsable', [AvanceSalireController::class, 'getAvanceApproveResponsable'])
        ->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('refusesResponsable', [AvanceSalireController::class, 'getAvanceRefuseResponsable'])
        ->middleware(['auth:api', 'role:ResponsableRh']);
    Route::get('attente', [AvanceSalireController::class, 'getAvanceAttenteAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);
    Route::get('acceptes', [AvanceSalireController::class, 'getAvanceApproveAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);
    Route::get('refuses', [AvanceSalireController::class, 'getAvanceRefuseAgentRH'])
        ->middleware(['auth:api', 'role:agentRh']);
    Route::get('user/{user_id}', [AvanceSalireController::class, 'getResultByUser'])
        ->middleware(['auth:api', 'role:employee,chefProjet']);
    Route::get('resultAgentRH/{user_id}', [AvanceSalireController::class, 'getResultByUserAgentRh'])->middleware(['auth:api', 'role:agentRh']);
});

Route::group(['prefix' => 'ai'], function () {

    Route::post('chatbot', [IaController::class, 'chatbot']);
});
