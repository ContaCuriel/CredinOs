<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeduccionEmpleado;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ActualizarDeducciones extends Command
{
    protected $signature = 'deducciones:actualizar {tenantId?} {--all}';
    
    protected $description = 'Procesa las deducciones activas para uno o todos los inquilinos.';

    public function handle()
    {
        $tenantId = $this->argument('tenantId');
        $allTenants = $this->option('all');

        if ($allTenants) {
            $tenants = Tenant::all();
        } elseif ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
        } else {
            $this->error('Debes especificar un ID de inquilino o usar la opción --all.');
            return 1;
        }

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron inquilinos para procesar.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando inquilino: {$tenant->name} (ID: {$tenant->id}) ---");
            
            try {
                $this->switchToTenantDatabase($tenant);
                $this->processDeductionsForCurrentTenant();
            } catch (\Exception $e) {
                $this->error("  -> Error al procesar el inquilino {$tenant->name}: " . $e->getMessage());
                $this->error("  -> En el archivo: " . $e->getFile() . " en la línea: " . $e->getLine());
            }
        }

        $this->info('--- ¡Proceso de actualización de deducciones completado! ---');
        return 0;
    }

    protected function switchToTenantDatabase(Tenant $tenant)
    {
        DB::purge('tenant');

        Config::set('database.connections.tenant.driver', 'pgsql');
        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.port', $tenant->db_port);
        // --- CORRECCIÓN 1 ---
        // Usamos 'db_database', el nombre correcto de la columna.
        Config::set('database.connections.tenant.database', $tenant->db_database);
        Config::set('database.connections.tenant.username', $tenant->db_username);
        Config::set('database.connections.tenant.password', $tenant->db_password);
        // --- CORRECCIÓN 2 ---
        // Forzamos el uso de encriptación SSL para cumplir con los requisitos de Render.
        Config::set('database.connections.tenant.sslmode', 'require');
        
        DB::reconnect('tenant');
        
        Config::set('database.default', 'tenant');
        
        $this->line("  -> Conectado a la base de datos: {$tenant->db_database} con el usuario: {$tenant->db_username}");
    }

    protected function processDeductionsForCurrentTenant()
    {
        $this->line('  -> Iniciando proceso de actualización de deducciones...');
        $hoy = Carbon::today();
        
        DeduccionEmpleado::where('status', 'Activo')->chunkById(100, function ($deduccionesActivas) use ($hoy) {
            foreach ($deduccionesActivas as $deduccion) {
                $fechaInicio = Carbon::parse($deduccion->fecha_solicitud);
                $ultimaReferencia = $deduccion->fecha_ultimo_descuento 
                    ? Carbon::parse($deduccion->fecha_ultimo_descuento) 
                    : $fechaInicio->copy()->subDay();

                if ($hoy->isAfter($ultimaReferencia)) {
                    $this->procesarQuincenasPendientes($deduccion, $ultimaReferencia, $hoy);
                }
            }
        });
        
        $this->line('  -> Actualización de deducciones para este inquilino completada.');
    }

    private function procesarQuincenasPendientes(DeduccionEmpleado $deduccion, Carbon $desde, Carbon $hasta)
    {
        $fechaIterador = $desde->copy();
        $fechaSolicitud = Carbon::parse($deduccion->fecha_solicitud);

        while ($fechaIterador->lessThan($hasta)) {
            $fechaIterador->addDay();

            if (($fechaIterador->day == 15 || $fechaIterador->isLastOfMonth()) && $fechaIterador >= $fechaSolicitud) {
                $this->line("    - Procesando deducción #{$deduccion->id} en fecha {$fechaIterador->toDateString()}");
                switch ($deduccion->tipo_deduccion) {
                    case 'Préstamo':
                        $deduccion->saldo_pendiente -= $deduccion->monto_quincenal;
                        $deduccion->quincenas_pagadas += 1;
                        if ($deduccion->saldo_pendiente <= 0) {
                            $deduccion->saldo_pendiente = 0;
                            $deduccion->status = 'Pagado';
                            $this->warn("      ¡Préstamo #{$deduccion->id} LIQUIDADO!");
                        }
                        break;
                    case 'Caja de Ahorro':
                        $deduccion->monto_acumulado += $deduccion->monto_quincenal;
                        break;
                }
                $deduccion->fecha_ultimo_descuento = $fechaIterador->copy();
                $deduccion->save(); 
            }
        }
    }
}

