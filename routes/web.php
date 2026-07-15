<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\MotorController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/simulatie', [SimulationController::class, 'index'])->name('simulation.index');
Route::get('/partners', [PageController::class, 'partners'])->name('partners.index');
Route::get('/partner-worden', [PageController::class, 'partnerApply'])->name('partners.apply');
Route::get('/over-ons', [PageController::class, 'about'])->name('about');
Route::get('/hoe-het-werkt', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
Route::get('/embed', [PageController::class, 'embed'])->name('embed');
Route::get('/s/{code}', [SimulationController::class, 'showShared'])->name('share.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/garage', [GarageController::class, 'index'])->name('garage.index');
    Route::post('/garage', [GarageController::class, 'store'])->name('garage.store');
    Route::delete('/garage/{garageMotor}', [GarageController::class, 'destroy'])->name('garage.destroy');
    Route::get('/profiel', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profiel', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/api/motors', [MotorController::class, 'search'])->name('api.motors.search');
Route::post('/api/motors/lookup', [MotorController::class, 'lookup'])
    ->middleware('throttle:15,1')
    ->name('api.motors.lookup');
Route::post('/api/motors/manual', [MotorController::class, 'storeManual'])
    ->middleware('throttle:15,1')
    ->name('api.motors.manual');
Route::get('/api/simulatie/limiet', [SimulationController::class, 'limit'])->name('api.simulation.limit');
Route::post('/api/simulatie', [SimulationController::class, 'run'])->name('api.simulation.run');

Route::get('/sitemap.xml', function () {
    return response()
        ->view('sitemap')
        ->header('Content-Type', 'application/xml');
})->name('sitemap');
