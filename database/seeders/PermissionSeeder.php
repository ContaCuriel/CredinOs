<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // --- RECURSOS HUMANOS ---
            'ver-menu-rh',
            'ver-empleados', 'crear-empleados', 'editar-empleados', 'eliminar-empleados',
            'ver-contratos', 'crear-contratos', 'imprimir-contratos', 'exportar-contratos',
            'ver-asistencias', 'registrar-asistencias', 'editar-asistencias',
            'ver-vacaciones', 'registrar-vacaciones',
            'ver-deducciones', 'crear-deducciones', 'editar-deducciones', 'eliminar-deducciones',
            'ver-lista-raya', 'exportar-lista-raya',
            'ver-finiquitos', 'calcular-finiquitos', 'exportar-finiquitos',
            'ver-gestion-imss', 'tramitar-imss',

            // --- CONTABILIDAD ---
            'ver-menu-contabilidad',
            'ver-aguinaldo', 'calcular-aguinaldo', 'exportar-aguinaldo',
            'ver-gastos','crear-gastos','editar-gastos','eliminar-gastos','aprobar-gastos',
            'ver-reportes',

            'ver-cuentas', 'crear-cuentas', 'editar-cuentas', 'eliminar-cuentas',

             // ===== NUEVOS PERMISOS PARA LAS PÓLIZAS =====
            'ver-polizas', 'ver-detalle-polizas',

            'ver-colocaciones',
            'ver-recuperaciones',

            'ver-aguinaldo', 'calcular-aguinaldo', 'exportar-aguinaldo',
            'ver-reportes',
            

        

            // --- ADMINISTRACIÓN ---
            'ver-menu-administracion',
            'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'eliminar-usuarios',
            'ver-roles', 'crear-roles', 'editar-roles', 'eliminar-roles',

            // --- CONFIGURACIÓN ---
            'ver-menu-configuracion',
            'ver-sucursales', 'crear-sucursales',
            'ver-puestos', 'crear-puestos', 'editar-puestos', 'eliminar-puestos',
            'ver-patrones', 'crear-patrones',
            'ver-horarios', 'crear-horarios', 'editar-horarios', 'eliminar-horarios',
         'ver-categorias','crear-categorias','editar-categorias','eliminar-categorias',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $rolSuperAdmin = Role::firstOrCreate(['name' => 'Super-Admin']);
        $rolSuperAdmin->givePermissionTo(Permission::all());

        $user = User::first();
        if ($user && !$user->hasRole('Super-Admin')) {
            $user->assignRole($rolSuperAdmin);
        }
    }
}