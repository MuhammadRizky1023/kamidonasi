<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\HomeController;
/*
Web Routes
--------------------------------------------------------------------------
Here is where you can register web routes for your application. These
routes are loaded by the RouteServiceProvider within a group which
contains the "web" middleware group. Now create something great!
*/


// login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// home
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/get-token', [HomeController::class, 'getToken'])->name('get.token');
Route::post('/notification-handler', [HomeController::class, 'notificationHandler'])->name('notification.handler');


//donation
Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
Route::get('/donations/create', [DonationController::class, 'create'])->name('donations.create');
Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');
Route::get('/donations/{id}/edit', [DonationController::class, 'edit'])->name('donations.edit');
Route::put('/donations/{id}', [DonationController::class, 'update'])->name('donations.update');
Route::delete('/donations/{id}', [DonationController::class, 'destroy'])->name('donations.destroy');



