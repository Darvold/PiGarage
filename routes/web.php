<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers as ControllersNamespace;

Route::get('/cc', function () {
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize');
    return 'Cache cleared successfully.';
});

Route::get('/', function () {
    return view('welcome');
})->name('welcome.index');

Route::get('/authorization', [ControllersNamespace\LoginAndRegistrController::class, 'loginUser'])->name('login.index');
Route::get('/registration', [ControllersNamespace\LoginAndRegistrController::class, 'registrUser'])->name('registr.index');
