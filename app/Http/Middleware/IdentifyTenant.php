<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;

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

        // --- CORRECCIÓN CLAVE AQUÍ ---
        // Cambiamos 'mysql' por 'pgsql' para que coincida con la base de datos de Render.
        $tenant = DB::connection('pgsql')->table('tenants')->where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Inquilino no encontrado.');
        }

        Config::set('database.connections.tenant', [
            'driver'    => 'pgsql', // Aseguramos que aquí también sea pgsql
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

        DB::setDefaultConnection('tenant');

        return $next($request);
    }
}
