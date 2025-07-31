<?php

use App\Http\Controllers\Auth\CheckCodeController;
use App\Http\Controllers\Auth\CheckTokenController;
use App\Http\Controllers\Auth\CheckWelcomeController;
use App\Http\Controllers\Auth\JoinController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CompleteRegisterController;
use App\Http\Controllers\Auth\ForgetPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/join', [JoinController::class, 'store']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register-complete', [CompleteRegisterController::class, 'update']);
Route::post('/welcome', CheckWelcomeController::class);
Route::post('/check-token', CheckTokenController::class);
Route::post('/forget-password', [ForgetPasswordController::class, 'store']);
Route::post('/check-code', [CheckCodeController::class, 'index']);
Route::post('/reset-password', [ResetPasswordController::class, 'update']);
Route::get('/test', function () {
    return response()->json([
        'message' => 'Laravel API is working!',
        'status' => 'success',
        'app_name' => config('app.name'),
        'timestamp' => now(),
        'database' => 'Connected to: ' . config('database.connections.mysql.database')
    ]);
});