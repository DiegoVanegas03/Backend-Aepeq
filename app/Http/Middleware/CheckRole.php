<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole{    
    public function handle($request, Closure $next, ...$roles){
        if (!in_array($request->user()->rol, $roles)) {
            return response()->json(['error' => 'No tienes permiso para realizar esta acción.'], 403);
        }
        return $next($request);
    }
}