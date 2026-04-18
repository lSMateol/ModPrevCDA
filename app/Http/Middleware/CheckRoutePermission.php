<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class CheckRoutePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Si no hay usuario, dejar que 'auth' lo maneje
        if (!$user) {
            return $next($request);
        }

        // 2. Si es Administrador con acceso total (por rol), permitir todo en admin.*
        if ($user->hasRole('Administrador')) {
            return $next($request);
        }

        // 3. Obtener el nombre de la ruta actual
        $routeName = Route::currentRouteName();

        // 4. Si la ruta no tiene nombre, permitir (estilo básico)
        if (!$routeName) {
            return $next($request);
        }

        // 5. Verificar si el usuario tiene permiso explícito para esta ruta
        // Usamos el nombre de la ruta como el nombre del permiso
        if ($user->hasPermissionTo($routeName)) {
            return $next($request);
        }

        // 6. Si no tiene permiso, abortar con 403
        abort(403, 'No tienes permisos para acceder a esta sección (' . $routeName . ')');
    }
}
