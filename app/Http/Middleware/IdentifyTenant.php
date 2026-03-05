<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Buscamos el inquilino en la base de datos central
        $tenant = DB::connection('pgsql')->table('tenants')->where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Inquilino no encontrado.');
        }

        DB::purge('tenant');

        // Configuramos la conexión dinámica
        Config::set('database.connections.tenant', [
            'driver'    => 'pgsql',
            'host'      => $tenant->db_host,
            'port'      => $tenant->db_port,
            'database'  => $tenant->db_database,
            'username'  => $tenant->db_username,
            'password'  => $tenant->db_password,
            'charset'   => 'utf8',
            'prefix'    => '',
            // IMPORTANTE: Aquí usamos el esquema específico del inquilino
            // Si tu tabla 'tenants' tiene una columna para el esquema, úsala: $tenant->db_schema
            // De lo contrario, usamos una lógica simple: si es Credintegra, usa su db, etc.
            'schema'    => $tenant->db_schema ?? $this->getSchemaByDomain($host),
            'sslmode'   => 'prefer',
        ]);

        DB::setDefaultConnection('tenant');

        return $next($request);
    }

    /**
     * Lógica de respaldo para identificar el esquema si no está en la tabla tenants.
     */
    private function getSchemaByDomain($host)
    {
        if (str_contains($host, 'credintegra')) {
            return 'credintegra_db';
        }
        if (str_contains($host, 'crediticia')) {
            return 'facturame_db';
        }
        
        return 'public'; // Valor por defecto
    }
}