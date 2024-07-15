<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Users::where('id', Auth::id())->first();

        if (Auth::check() == Auth::id() && $user->id_al == 1) {
            // Пользователь авторизован и имеет правильный id и уровень доступа
            return $next($request);
        }


        if (!Auth::check()) {
            // Пользователь не авторизован, перенаправляем на страницу входа
            return redirect()->route('login.index')->with('error', 'Пользователь не найден');
        } else {
            // Пользователь авторизован, но не имеет правильного id или уровня доступа
            return redirect()->back();
        }
    }
}
