<?php

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Backoffice;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Controllers\Backoffice\UserController;

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

// AUTHENTIFICATION
Route::middleware([RedirectIfAuthenticated::class])->group(function () {
    Route::get('/login', [Backoffice\LoginController::class, 'show'])->name('login');
    Route::post('/login', [Backoffice\LoginController::class, 'login'])->name('login.perform');

    // Affiche le formulaire d'inscription
    Route::get('/register', [Backoffice\RegisterController::class, 'create'])->name('register');
    // Gère la soumission du formulaire d'inscription
    Route::post('/register', [Backoffice\RegisterController::class, 'store'])->name('register.perform');

    Route::get('/forgot-password', [Backoffice\ResetPassword::class, 'create'])->name('reset-password');
    Route::post('/forgot-password', [Backoffice\ResetPassword::class, 'send'])->name('password.email');
});

Route::post('/logout', [Backoffice\LoginController::class, 'logout'])->name('logout');

// Routes du backoffice
Route::middleware(['auth'])->prefix('backoffice')->group(function () {
    Route::get('/dashboard', [Backoffice\HomeController::class, 'index'])->name('backoffice.dashboard');
    Route::get('/profile', [Backoffice\HomeController::class, 'show'])->name('profile');

    Route::get('/page/{page}', [Backoffice\PageController::class, 'index'])->name('page');
    Route::get('/virtual-reality', [Backoffice\PageController::class, 'vr'])->name('page.vr');
    Route::get('/rtl', [Backoffice\PageController::class, 'rtl'])->name('page.rtl');
    Route::get('/profile', [Backoffice\PageController::class, 'profile'])->name('page.profile');
    Route::get('/sign-in', [Backoffice\PageController::class, 'signin'])->name('page.signin');
    Route::get('/sign-up', [Backoffice\PageController::class, 'signup'])->name('page.signup');
});


Route::middleware(['auth', 'role:admin'])->prefix('backoffice')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
});
