<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear roles y permisos cacheados
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- CREACIÓN DE PERMISOS ORGANIZADOS POR GRUPO ---
        $permissionsByGroup = [
            'Dashboard' => [
                'ver-widget-contratos-vencer',
                'ver-widget-cumpleanos',
                'ver-widget-aniversarios',
                'ver-widget-nuevos-ingresos',
                'ver-widget-imss',
                'ver-widget-accesos-rapidos',
            ],
            'Menús Principales' => [
                'ver-menu-creditos',
                'ver-menu-rh',
                'ver-menu-contabilidad',
                'ver-menu-administracion',
                'ver-menu-configuracion',
            ],
            'Créditos y Cobranza' => [
                'ver-clientes', 'crear-clientes', 'editar-clientes', 'eliminar-clientes',
                'ver-grupos', 'crear-grupos', 'editar-grupos', 'eliminar-grupos',
                'ver-creditos', 'registrar-credito', 'editar-credito', 'eliminar-credito',
                'aprobar-credito', 'desembolsar-credito',
            ],
            'Recursos Humanos' => [
                'ver-empleados', 'crear-empleados', 'editar-empleados', 'eliminar-empleados',
                'ver-contratos', 'crear-contratos', 'imprimir-contratos', 'exportar-contratos',
                'ver-asistencias', 'registrar-asistencias', 'editar-asistencias',
                'ver-vacaciones', 'registrar-vacaciones',
                'ver-deducciones', 'crear-deducciones', 'editar-deducciones', 'eliminar-deducciones',
                'ver-lista-raya', 'exportar-lista-raya',
                'ver-finiquitos', 'calcular-finiquitos', 'exportar-finiquitos',
                'ver-gestion-imss', 'tramitar-imss',
                'ver-aguinaldo', 'calcular-aguinaldo', 'exportar-aguinaldo',
                'ver-renuncias','generar-renuncias',
            ],
            'Contabilidad' => [
                'ver-gastos', 'crear-gastos', 'editar-gastos', 'eliminar-gastos', 'aprobar-gastos',
                'ver-reportes',
                'ver-cuentas', 'crear-cuentas', 'editar-cuentas', 'eliminar-cuentas',
                'ver-polizas', 'ver-detalle-polizas',
                'ver-colocaciones',
                'ver-recuperaciones',
            ],
            'Administración' => [
                'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'eliminar-usuarios',
                'ver-roles', 'crear-roles', 'editar-roles', 'eliminar-roles',
            ],
            'Configuración del Sistema' => [
                'ver-sucursales', 'crear-sucursales', 'editar-sucursales', 'eliminar-sucursales',
                'ver-puestos', 'crear-puestos', 'editar-puestos', 'eliminar-puestos',
                'ver-patrones', 'crear-patrones', 'editar-patrones', 'eliminar-patrones',
                'ver-horarios', 'crear-horarios', 'editar-horarios', 'eliminar-horarios',
                'ver-categorias', 'crear-categorias', 'editar-categorias', 'eliminar-categorias',
                'ver-tipos-credito', 'crear-tipos-credito', 'editar-tipos-credito', 'eliminar-tipos-credito',
                'ver-tasas-interes', 'crear-tasas-interes', 'editar-tasas-interes', 'eliminar-tasas-interes',
            ],

            'Módulo de Prueba' => [
                'ver-modulo-prueba',
                'usar-modulo-prueba',
            ],  
        ];

        // Crear los permisos en la base de datos
        foreach ($permissionsByGroup as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }
        }

        // --- CREACIÓN DE ROL Y USUARIO SUPER-ADMIN ---
        $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        if (!User::where('email', 'superadmin@credinos.com')->exists()) {
            User::create([
                'name' => 'Super Administrador',
                'email' => 'superadmin@credinos.com',
                'password' => Hash::make('password')
            ])->assignRole($superAdminRole);
        }
    }
}