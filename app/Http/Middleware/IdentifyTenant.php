<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // --- CORRECCIÓN DEFINITIVA ---
        // Usamos explícitamente la conexión 'pgsql' (configurada en las variables de entorno)
        // para buscar al inquilino, evitando cualquier conflicto con la caché.
        $tenant = DB::connection('pgsql')->table('tenants')->where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Inquilino no encontrado.');
        }

        // Purgamos cualquier conexión de inquilino antigua para asegurar un estado limpio.
        DB::purge('tenant');

        // Configuramos la nueva conexión para el inquilino encontrado.
        Config::set('database.connections.tenant', [
            'driver'    => 'pgsql',
            'host'      => $tenant->db_host,
            'port'      => $tenant->db_port,
            'database'  => $tenant->db_database,
            'username'  => $tenant->db_username,
            'password'  => $tenant->db_password,
            'charset'   => 'utf8',
            'prefix'    => '',
            'schema'    => 'public',
            'sslmode'   => 'prefer',
        ]);

        // Establecemos la conexión del inquilino como la predeterminada para el resto de la solicitud.
        DB::setDefaultConnection('tenant');

        // =====> INICIO DE AISLAMIENTO DE CACHÉ DE PERMISOS <=====
        // 1. Le decimos a Spatie que use un nombre de caché único basado en el ID de este inquilino
        $cacheKey = 'spatie.permission.cache.tenant_' . $tenant->id;
        app(\Spatie\Permission\PermissionRegistrar::class)->setCacheKey($cacheKey);

        // 2. Limpiamos la memoria local de esta petición para forzar que cargue 
        // los permisos de la nueva base de datos y no se quede con la del inquilino anterior.
        app(\Spatie\Permission\PermissionRegistrar::class)->clearClassPermissions();
        // =====> FIN DE AISLAMIENTO DE CACHÉ DE PERMISOS <=====

        return $next($request);
    }
}