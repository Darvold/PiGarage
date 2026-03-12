<?php

namespace App\Http\Middleware;

use App\Models\Cooperatives;
use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $textInfo = "Ваша сессия истекла или отсутствует. Пожалуйста, войдите снова.";
        $textError = "Пользователю не принадлежит указанный кооператив.";

        // Проверка, что пользователь авторизован
        if (Auth::check()) {
/*            $userId = Auth::id();
            $user = Users::where('id', $userId)->first();*/
            return $next($request);
            /*// Проверка уровня доступа пользователя
            if ($user && $user->id_al == 2) {

                // Проверка на наличие id_coop в запросе
                $idCoop = $request->input('id_coop') ?? $request->route('id_coop');
                if ($idCoop) {
                    // Проверяем, существует ли кооператив с указанным id_coop и привязан ли он к авторизованному пользователю
                    $coop = Cooperatives::where('id_coop', $idCoop)
                        ->where('user_id', $userId)
                        ->first();

                    // Если кооператив не найден или не принадлежит пользователю, возвращаем ошибку
                    if (!$coop) {
                        return $this->unauthorizedResponse($request, $textError);
                    }
                }

                return $next($request);
            } else {
                // Если уровень доступа не соответствует
                return $this->unauthorizedResponse($request, $textInfo);
            }*/
        } else {
            // Если пользователь не авторизован
            return $this->unauthorizedResponse($request, $textInfo);
        }

    }
    protected function unauthorizedResponse($request, $textInfo)
    {
        if ($request->ajax() || $request->wantsJson()) {
            session()->flash('info', $textInfo);
            return response()->json(['redirect' => route('login.index')], 403);
        }

        return redirect()->route('login.index');
    }

}
