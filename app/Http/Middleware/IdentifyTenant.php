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

        $tenant = DB::connection('pgsql')->table('tenants')->where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Inquilino no encontrado.');
        }

        DB::purge('tenant');

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

        DB::setDefaultConnection('tenant');

        return $next($request);
    }
}