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
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();
    
            // Rediriger vers la page de vérification si l'utilisateur n'a pas encore vérifié son e-mail
            if (!$user->hasVerifiedEmail()) {
                return redirect('/email/verify');
            }
    
            // Sinon, rediriger vers le tableau de bord
            return redirect('/backoffice/dashboard');
        }
    
        return $next($request);
    }
    
}
