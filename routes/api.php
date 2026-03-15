<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\TacheController;
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Auth
Route::group(['prefix' => 'auth'],  function () {
    Route::put('restarpasword/{email}', [AuthController::class, 'restarpassword']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['auth:api','role:admin,employee,RH']);
    Route::put('updatepassword/{id}', [AuthController::class, 'updatepassword']);
    Route::get('User/{id}', [AuthController::class, 'getUserById'])->middleware(['auth:api', 'role:admin,employee,RH']);
    Route::put('verifMail/{id}', [AuthController::class, 'verifMail']);
    Route::put('updateUser/{id}', [AuthController::class, 'UpdateUser'])->middleware(['auth:api','role:admin,employee,RH,ChefProjet']);
    Route::put('updatepassword1/{id}', [AuthController::class, 'updatePassword1'])->middleware(['auth:api','role:admin,employee,ChefProjet,RH']);
    Route::get('employees', [AuthController::class, 'index'])->middleware(['auth:api','role:RH']);
    Route::get('getData', [AuthController::class, 'getData'])->middleware(['auth:api','role:admin']);
    Route::post('/ouvrir-session', [AuthController::class, 'ouvrirSession'])->middleware(['auth:api','role:employee,RH,ChefProjet']);
    Route::post('/fermer-session', [AuthController::class, 'fermerSession'])->middleware(['auth:api','role:employee,RH,ChefProjet']);
    Route::get('/pause-dejeuner', [AuthController::class, 'pauseDejeuner'])->middleware(['auth:api','role:employee,RH,ChefProjet']);

    Route::get('statUser', [AuthController::class, 'statUser'])->middleware(['auth:api','role:admin,RH']);
    Route::put('activeAccount/{id}', [AuthController::class, 'activeAccount'])->middleware(['auth:api', 'role:admin,RH']);
    Route::put('AccounNotActive/{id}', [AuthController::class, 'AccounNotActive'])->middleware(['auth:api', 'role:admin,RH']);
    Route::get('session-status', [AuthController::class, 'getSessionStatus'])->middleware(['auth:api','role:employee,ChefProjet,RH']);
    Route::get('searchUsers/search', [RhController::class, 'searchUsers'])->middleware(['auth:api','role:RH,admin']);

});
//departements
Route::group(['prefix' => 'departements'], function () {
    Route::get('get_departements', [DepartementController::class, 'index'])->middleware(['auth:api', 'role:admin,RH,ChefProjet']);
    Route::get('get_departements/{id}', [DepartementController::class, 'getdepartementsById'])->middleware(['auth:api', 'role:admin']);
    Route::post('add_departement', [DepartementController::class, 'store'])->middleware(['auth:api', 'role:admin']);
    Route::put('update_departement/{id}', [DepartementController::class, 'update'])->middleware(['auth:api', 'role:admin']);
    Route::delete('delete_departement/{id}', [DepartementController::class, 'destroy'])->middleware(['auth:api', 'role:admin']);
    Route::get('searchDepartement/search', [DepartementController::class, 'search'])->middleware(['auth:api', 'role:admin']);
});
//absence
Route::group(['prefix' => 'absence'],  function () {
    Route::post('verifier-absences', [AuthController::class, 'Absence'])->middleware('role:admin,RH');
    Route::get('absence/{id}', [AuthController::class, 'absenceEmployee'])->middleware('role:admin,RH,employee');
    Route::post('reset-salaire', [AuthController::class, 'resetSalaireMensuel'])->middleware('role:admin');
    Route::get('most-absent', [StatistiqueController::class, 'EmployeePlusAbsent'])->middleware('role:admin,RH');
    Route::get('most-present', [StatistiqueController::class, 'EmployeePlusPresent'])->middleware('role:admin,RH');
});
Route::group(['prefix' => 'stat'],  function () {
    Route::get('count-departements', [StatistiqueController::class, 'CountDepartement'])->middleware('role:admin,RH');
    Route::get('count-employees', [StatistiqueController::class, 'CountEmployee'])->middleware('role:admin,RH');
    Route::get('employees-present-today', [StatistiqueController::class, 'EmployeesPresentToday'])->middleware(['auth:api','role:admin,RH']);
    Route::get('employees-present-list', [StatistiqueController::class, 'EmployeesPresentList'])->middleware(['auth:api','role:admin,RH']);
    Route::get('employees-absent-today', [StatistiqueController::class, 'EmployeesAbsentToday'])->middleware(['auth:api','role:admin,RH']);
    Route::get('most-absent-last-month', [StatistiqueController::class, 'EmployeLePlusAbsentMoisDernier']);
    Route::get('most-present-last-month', [StatistiqueController::class, 'EmployeLePlusPresentMoisDernier']);
    Route::get('employees-absent-list', [StatistiqueController::class, 'EmployeesAbsentTodayList'])
    ->middleware(['auth:api','role:admin,RH']);
});
Route::group(['prefix' => 'taches'],  function () {
    Route::get('get_taches', [TacheController::class, 'index'])->middleware('role:ChefProjet,employee');
    Route::get('get_tache/{id}', [TacheController::class, 'show'])->middleware('role:ChefProjet');
    Route::post('add_tache', [TacheController::class, 'store'])->middleware('role:ChefProjet');
    Route::put('update_tache/{id}', [TacheController::class, 'update'])->middleware('role:ChefProjet,employee');
    Route::delete('delete_tache/{id}', [TacheController::class, 'destroy'])->middleware('role:ChefProjet');
    Route::get('searchTache/search', [TacheController::class, 'search'])->middleware('role:ChefProjet');
    Route::get('/employees/{id}', [TacheController::class, 'getEmployeesByDepartement'])->middleware(['auth:api','role:ChefProjet']);
    Route::get('getTacheByUserId/{user_id}', [TacheController::class, 'getTacheByUserId'])->middleware(['auth:api','role:employee']);

});


