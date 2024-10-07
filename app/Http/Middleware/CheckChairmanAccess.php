<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckChairmanAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $textInfo = "Ваша сессия истекла или отсутствует. Пожалуйста, войдите снова.";
        if (Auth::check()) {
       // $id = $request->route('id');
        $user = Users::where('id', Auth::id())->first();

        if (Auth::check() == Auth::id() && $user->id_al == 2) {
            // Пользователь авторизован и имеет правильный id и уровень доступа
            return $next($request);
        } else {
            // Пользователь авторизован, но не имеет нужного уровня доступа
            if ($request->ajax() || $request->wantsJson()) {
                // Возвращаем JSON-ответ с указанием, что требуется авторизация
                session()->flash('info', $textInfo);
                return response()->json(['redirect' => route('login.index')], 403);
            }
            return redirect()->route('login.index')->with('info', $textInfo);
        }
        } else {
            // Пользователь авторизован, но не имеет нужного уровня доступа
            if ($request->ajax() || $request->wantsJson()) {
                // Возвращаем JSON-ответ с указанием, что требуется авторизация
                session()->flash('info', $textInfo);
                return response()->json(['redirect' => route('login.index')], 403);
            }
            // Пользователь не авторизован
            return redirect()->route('login.index')->with('info', $textInfo);
        }
    }
}
