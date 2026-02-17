<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\AuthController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Auth
Route::group(['prefix' => 'auth'],  function () {
    Route::put('restarpasword/{email}', [AuthController::class, 'restarpassword']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('updatepassword/{id}', [AuthController::class, 'updatepassword']);
    Route::get('User/{id}', [AuthController::class, 'getUserById']);
    Route::put('verifMail/{id}', [AuthController::class, 'verifMail']);
    Route::put('updateUser/{id}', [AuthController::class, 'UpdateUser'])->middleware(['auth:api','role:admin,employee']);
    // Route::get('/absence', [AuthController::class, 'absence']);
    Route::put('updatepassword1/{id}', [AuthController::class, 'updatePassword1'])->middleware(['auth:api','role:admin,employee']);
    Route::get('employees', [AuthController::class, 'index'])->middleware(['auth:api','role:admin']);
    Route::post('/ouvrir-session', [AuthController::class, 'ouvrirSession'])->middleware(['auth:api','role:employee']);
    Route::post('/fermer-session', [AuthController::class, 'fermerSession'])->middleware(['auth:api','role:employee']);
    Route::get('statUser', [AuthController::class, 'statUser'])->middleware(['auth:api','role:admin']);
});

