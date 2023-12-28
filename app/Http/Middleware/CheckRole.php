<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (! $request->user()->hasRole($role)) {
            // Aquí puedes manejar el caso en el que el usuario no tiene el rol requerido.
            // Por ejemplo, puedes devolver una respuesta con un mensaje de error.
            return response()->json(['error' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        return $next($request);
    }
}
