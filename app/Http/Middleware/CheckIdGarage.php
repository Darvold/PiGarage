<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class CheckIdGarage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Users::where('id', Auth::id())->with('garages')->first();

        $idGarage = $request->route('idGarage');
        $hasAccess = $user->garages->contains('id_garage', $idGarage);

        if ($hasAccess) {
            // Пользователь имеет доступ к этому гаражу
            return $next($request);
        }

        // Если пользователь не имеет доступа, перенаправляем его на предыдущую страницу
        return Redirect::back();
    }
}
