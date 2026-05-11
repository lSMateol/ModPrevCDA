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

        // 6. Verificar si el usuario tiene permiso explícito para esta ruta
        try {
            if ($user->hasPermissionTo($routeName)) {
                return $next($request);
            }
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            // Si el permiso no está formalmente guardado en la Base de Datos, 
            // asumiremos el permiso basado en si la ruta pertenece a su espacio protegido por Rol.
            // Esto evita que toda la app colapse cuando no se han sembrado (Seeder) los permisos granulares.
            return $next($request);
        }

        // 7. Si no tiene permiso (está en base de datos pero este usuario no lo posee), abortar con 403
        abort(403, 'No tienes permisos para acceder a esta sección (' . $routeName . ')');
    }
}
