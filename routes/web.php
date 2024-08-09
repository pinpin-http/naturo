<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backoffice;

// Routes Frontoffice
Route::get('/', function () {
    return view('frontoffice.index');
})->name('home');

Route::get('/about', function () {
    return view('frontoffice.about');
});

Route::get('/contact', function () {
    return view('frontoffice.contact');
});

Route::get('/service', function () {
    return view('frontoffice.service');
});

Route::get('/team', function () {
    return view('frontoffice.team');
});

Route::get('/testimonial', function () {
    return view('frontoffice.testimonial');
});

Route::get('/appointment', function () {
    return view('frontoffice.appointment');
});

Route::get('/price', function () {
    return view('frontoffice.price');
});


//AUTHENTIFICATION
Route::get('/login', [Backoffice\LoginController::class, 'show'])->name('login');
Route::post('/login', [Backoffice\LoginController::class, 'login'])->name('login.perform');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/forgot-password', [ResetPassword::class, 'show'])->name('reset-password');
Route::post('/forgot-password', [ResetPassword::class, 'send'])->name('password.email');



Route::middleware(['auth'])->prefix('backoffice')->group(function () {
    Route::get('/dashboard', [Backoffice\DashboardController::class, 'index'])->name('backoffice.dashboard');
    // Ajoutez d'autres routes ici
});
