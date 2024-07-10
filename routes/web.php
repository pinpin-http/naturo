<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('frontoffice.index');
});

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
