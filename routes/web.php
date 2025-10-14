<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserpageController;
use App\Http\Controllers\PilihKamarController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::post('/login', [AuthController::class, 'login']);

// Backward compatibility: old dashboard URL
Route::redirect('/dashboard', '/admin/dashboard');
Route::redirect('/userpage', '/user/userpage');

Route::group(['middleware' => ['auth', 'check_role:admin'], 'prefix' => 'admin'], function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/export', [DashboardController::class, 'export'])->name('dashboard.export');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::get('/rooms/{room}/payments', [RoomController::class, 'payments'])->name('rooms.payments');
    Route::post('/admin/payments/{payment}/approve', [RoomController::class, 'approvePayment'])->name('payments.approve');
    Route::post('/admin/payments/{payment}/reject', [RoomController::class, 'rejectPayment'])->name('payments.reject');


    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    Route::patch('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
    Route::patch('/tenants/{tenant}/deactivate', [TenantController::class, 'deactivate'])->name('tenants.deactivate');
    Route::patch('/tenants/{tenant}/activate-room', [TenantController::class, 'activateRoom'])->name('tenants.activate-room');
    Route::patch('/tenants/{tenant}/deactivate-room', [TenantController::class, 'deactivateRoom'])->name('tenants.deactivate-room');
    Route::get('/tenants/{tenant}/payments', [TenantController::class, 'payments'])->name('tenants.payments');

    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

//Route::middleware(['auth', 'pilih.kamar'])->get('/userpage', [UserpageController::class, 'index'])->name('userpage');

Route::group(['middleware' => ['auth', 'check_role:user'], 'prefix' => 'user'], function() {
    Route::get('/userpage', [UserpageController::class, 'index'])->name('userpage');
    Route::get('/choose-room', [PilihKamarController::class, 'index'])->name('pilih.kamar');
    Route::post('/choose-room', [PilihKamarController::class, 'store'])->name('pilih.kamar.store');

    //transaksi
    //Route::resource('payment', PaymentController::class);
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('/payment/create', [PaymentController::class, 'create'])->name('payment.create');
    Route::post('/payment/upload/{id}', [PaymentController::class, 'uploadProof'])->name('payment.upload');
    Route::post('/payment/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/logout', [AuthController::class, 'logout']);
