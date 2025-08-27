<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant; // Importaremos este modelo más adelante

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
        // 1. Obtenemos el dominio de la petición (ej: "credintegra.localhost")
        $host = $request->getHost();

        // 2. Buscamos en la base de datos CENTRAL si existe un inquilino con ese dominio.
        // Usamos una consulta directa para asegurarnos de que siempre se busca en la conexión por defecto.
        $tenant = DB::connection('mysql')->table('tenants')->where('domain', $host)->first();

        // 3. Si no encontramos al inquilino, la petición no es válida.
        // Mostramos un error 404 "No encontrado".
        if (!$tenant) {
            abort(404, 'Inquilino no encontrado.');
        }

        // 4. ¡Encontramos al inquilino! Ahora configuramos su conexión de base de datos.
        // Le decimos a Laravel que cree una nueva conexión "temporal" llamada 'tenant'.
        Config::set('database.connections.tenant', [
            'driver'    => 'mysql',
            'host'      => $tenant->db_host,
            'port'      => $tenant->db_port,
            'database'  => $tenant->db_database,
            'username'  => $tenant->db_username,
            'password'  => $tenant->db_password,
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);

        // 5. Finalmente, le decimos a Laravel que a partir de este momento,
        // la conexión por defecto ya no es 'mysql', sino nuestra nueva conexión 'tenant'.
        DB::setDefaultConnection('tenant');

        // 6. Dejamos que la petición continúe su curso normal.
        // Todos los modelos y consultas a partir de ahora usarán la base de datos del inquilino.
        return $next($request);
    }
}
