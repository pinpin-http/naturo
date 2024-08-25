<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;
use Closure;
use Illuminate\Http\Request;

class TestMiddleware
{
    public function handle(Request $request, Closure $next)
        {
        // Juste pour vérifier si ce middleware fonctionne
        Log::info('Middleware atteint');
        dd('Middleware atteint');
        return response('Middleware fonctionne !', 200);
        return $next($request);
    }
}
