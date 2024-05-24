<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {

/*        $id = $request->route('idAdmin');

        if (Auth::guard('admin')->check() && Auth::guard('admin')->id() == $id) {
            return $next($request);
        }

        if (Auth::guard('admin')->check() == null) {
            return redirect()->route('AdminLogin.index');
        } else {
            return redirect()->route('AdminMain.index', ['idAdmin' => Auth::guard('admin')->id()]);
        }*/
        $id = $request->route('idAdmin');

        if (Auth::check() && Auth::id() == $id) {
            return $next($request);
        }
        if (Auth::check() == null) {
            return redirect()->route('AdminLogin.index');
        } else {
            return redirect()->route('AdminMain.index', ['idAdmin' => Auth::id()]);
        }

    }
}
