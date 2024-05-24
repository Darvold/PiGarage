<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;

Route::get('/AdminUser/Registr', 'AdminController@RegistrNewAdmin')
    ->name('AdminRegistr.index');
Route::post('/AdminUser/Registr', 'AdminController@RegistrNewAdminInsert')
    ->name('AdminRegistrAdd.store');

Route::get('/AdminUser/Login',  'AdminController@LoginAdmin')
    ->name('AdminLogin.index');
Route::post('/AdminUser/Login/CreateNewSession', 'AdminController@LoginAdminNewSession')
    ->name('AdminLoginAdd.store');

Route::middleware([AdminMiddleware::Class])->group(function () {
        // Здесь маршруты для администраторов
        Route::get('/AdminUser/{idAdmin}/Main', 'AdminPagesController@AdminMain')
            ->name('AdminMain.index');

        Route::post('/AdminUser/{idAdmin}/Main/Application/NewCreateCoop', 'AdminPagesController@AddNewCoop')
            ->name('AddNewCoop.store');

        Route::post('/AdminUser/{idAdmin}/Logout', 'AdminPagesController@LogoutAdmin')
            ->name('LogoutAdmin.store');
});

