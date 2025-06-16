<?php

namespace App\Http\Middleware;

use App\Models\Tenant; // Tu modelo de Tenant, que es correcto.
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByInput
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Validar que el campo 'name' del formulario no venga vacío.
        if (!$request->filled('name')) {
            return back()->withErrors(['name' => 'El Nombre de Empresa es obligatorio.']);
        }

        $tenantIdentifier = $request->input('name');

        // 2. Buscar el tenant en la base de datos central por la columna 'name'.
        // Tu modelo ya está configurado para usar la conexión 'landlord', así que esto funcionará.
        $tenant = Tenant::where('name', $tenantIdentifier)->first();

        // 3. Verificar si se encontró el tenant y establecerlo como el actual.
        if ($tenant) {
            // ¡ESTA ES LA LÍNEA CLAVE PARA SPATIE!
            // Establece el tenant encontrado como el "actual" para la aplicación.
            $tenant->makeCurrent();
        } else {
            // Si no se encuentra el tenant, regresa al login con un error.
            return back()->withInput()->withErrors(['name' => 'La empresa o sucursal no existe.']);
        }

        // 4. Si todo salió bien, la petición continúa hacia el controlador de login.
        return $next($request);
    }
}