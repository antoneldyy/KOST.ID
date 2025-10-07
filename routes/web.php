<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

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

Route::get('/login', fn () => view('auth.login'))->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', fn () => 'halaman user');

Route::group(['middleware' => ['auth', 'check_role:admin']], function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});


Route::get('/logout', [AuthController::class, 'logout']);
