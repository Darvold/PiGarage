<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (Auth::guard('admin')->check()) {
            return route('AdminLogin.index'); // Если администратор авторизован, перенаправить на админ-панель
        } elseif (Auth::guard('web')->check()) {
            return route('login.index'); // Если пользователь авторизован, перенаправить на страницу пользователя
        }
        return null; // В других случаях оставить без перенаправления
    }
}
