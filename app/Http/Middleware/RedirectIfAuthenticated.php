<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
{
    // Si l'utilisateur est connecté et tente d'accéder à la route de réinitialisation de mot de passe
    if (Auth::check() && !$request->is('forgot-password')) {
        return redirect('/backoffice/dashboard');
    }

    return $next($request);
}

}
