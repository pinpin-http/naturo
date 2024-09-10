<?php
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
//controller backoffice 
use App\Http\Controllers\Backoffice\LoginController;
use App\Http\Controllers\Backoffice\RegisterController;
use App\Http\Controllers\Backoffice\ResetPassword;
use App\Http\Controllers\Backoffice\HomeController;
use App\Http\Controllers\Backoffice\LogController;
use App\Http\Controllers\Backoffice\UserController;
use App\Http\Controllers\Backoffice\PageController;




use App\Http\Controllers\Auth\VerificationController;

Auth::routes(['verify' => true]); // Inclut les routes nécessaires pour la vérification des e-mails
Route::get('/home', function () {
    return redirect('/backoffice/dashboard');
});

// Les routes qui ne nécessitent pas de vérification d'email
Route::middleware([RedirectIfAuthenticated::class])->group(function () {

    // Connexion et inscription
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.perform');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.perform');

    // Réinitialisation de mot de passe
    Route::get('/forgot-password', [ResetPassword::class, 'show'])->name('reset-password');
    Route::post('/forgot-password', [ResetPassword::class, 'send'])->name('password.email');
    Route::get('/reset/{token}', [ResetPassword::class, 'showResetForm'])->name('backoffice.password.reset'); // Modifie le nom ici
    Route::post('/reset-password', [ResetPassword::class, 'reset'])->name('backoffice.password.update');
    
   
});


//authentifiaction google
// Rediriger vers Google pour l'authentification
Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
// Gérer le callback de Google
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);




 // Vérification d'e-mail (ne doit pas être protégée par le middleware 'verified')
 Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
 Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
     ->name('verification.verify')
     ->middleware(['signed', 'auth']); // 'auth' pour s'assurer que l'utilisateur est connecté
 Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
// Déconnexion
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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

// Routes Backoffice
Route::middleware(['auth', 'verified'])->prefix('backoffice')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('backoffice.dashboard');
    Route::get('/profile', [HomeController::class, 'show'])->name('profile');

    Route::get('/page/{page}', [PageController::class, 'index'])->name('page');
    Route::get('/profile', [PageController::class, 'profile'])->name('page.profile');
    Route::put('/profile', [UserController::class, 'update'])->name('profile.update');
});

// Routes Admin (pour les utilisateurs avec le rôle 'admin')
Route::middleware(['auth', 'role:admin'])->prefix('backoffice')->group(function () {
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/search', [LogController::class, 'search'])->name('logs.search');
    Route::get('/users', [UserController::class, 'index'])->name('backoffice.users');
    Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
