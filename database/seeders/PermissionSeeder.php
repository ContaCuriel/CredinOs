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

        // ------------------------------------------------------------------
        // CREACIÓN DE PERMISOS
        // ------------------------------------------------------------------
        $permissions = [
            // --- MENÚS PRINCIPALES ---
            'ver-menu-creditos',
            'ver-menu-rh',
            'ver-menu-contabilidad',
            'ver-menu-administracion',
            'ver-menu-configuracion',

            // --- MÓDULO DE CRÉDITOS Y COBRANZA ---
            'ver-clientes', 'crear-clientes', 'editar-clientes', 'eliminar-clientes',
            'ver-grupos', 'crear-grupos', 'editar-grupos', 'eliminar-grupos',
            'ver-creditos', 'registrar-credito', 'editar-credito', 'eliminar-credito',
            'aprobar-credito', 'desembolsar-credito',

            // --- RECURSOS HUMANOS ---
            'ver-empleados', 'crear-empleados', 'editar-empleados', 'eliminar-empleados',
            'ver-contratos', 'crear-contratos', 'imprimir-contratos',
            'ver-asistencias', 'registrar-asistencias', 'editar-asistencias',
            'ver-vacaciones', 'registrar-vacaciones',
            'ver-deducciones', 'crear-deducciones', 'editar-deducciones', 'eliminar-deducciones',
            'ver-lista-raya', 'exportar-lista-raya',
            'ver-finiquitos', 'calcular-finiquitos',
            'ver-gestion-imss', 'tramitar-imss',
            'ver-aguinaldo', 'calcular-aguinaldo',

            // --- CONTABILIDAD ---
            'ver-gastos', 'crear-gastos', 'editar-gastos', 'eliminar-gastos', 'aprobar-gastos',
            'ver-reportes',
            'ver-cuentas', 'crear-cuentas', 'editar-cuentas', 'eliminar-cuentas',
            'ver-polizas', 'ver-detalle-polizas',
            'ver-colocaciones',
            'ver-recuperaciones',

            // --- ADMINISTRACIÓN ---
            'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'eliminar-usuarios',
            'ver-roles', 'crear-roles', 'editar-roles', 'eliminar-roles',

            // --- CONFIGURACIÓN DEL SISTEMA ---
            'ver-sucursales', 'crear-sucursales', 'editar-sucursales', 'eliminar-sucursales',
            'ver-puestos', 'crear-puestos', 'editar-puestos', 'eliminar-puestos',
            'ver-patrones', 'crear-patrones', 'editar-patrones', 'eliminar-patrones',
            'ver-horarios', 'crear-horarios', 'editar-horarios', 'eliminar-horarios',
            'ver-categorias', 'crear-categorias', 'editar-categorias', 'eliminar-categorias',
            'ver-tipos-credito', 'crear-tipos-credito', 'editar-tipos-credito', 'eliminar-tipos-credito',
            'ver-tasas-interes', 'crear-tasas-interes', 'editar-tasas-interes', 'eliminar-tasas-interes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ------------------------------------------------------------------
        // CREACIÓN DE ROLES Y ASIGNACIÓN DE PERMISOS
        // ------------------------------------------------------------------

        // Rol de Asesor: Operación de campo
        $asesorRole = Role::firstOrCreate(['name' => 'Asesor']);
        $asesorRole->syncPermissions([
            'ver-menu-creditos',
            'ver-clientes', 'crear-clientes', 'editar-clientes',
            'ver-grupos', 'crear-grupos', 'editar-grupos',
            'ver-creditos', 'registrar-credito',
        ]);

        // Rol de Gerente: Supervisión y aprobación
        $gerenteRole = Role::firstOrCreate(['name' => 'Gerente']);
        $gerenteRole->syncPermissions(array_merge($asesorRole->permissions->pluck('name')->toArray(), [
            'eliminar-clientes', 'eliminar-grupos',
            'editar-credito', 'eliminar-credito',
            'aprobar-credito', 'desembolsar-credito',
            'ver-menu-rh', 'ver-empleados', 'ver-contratos', 'ver-asistencias',
            'ver-menu-contabilidad', 'ver-reportes', 'ver-colocaciones', 'ver-recuperaciones',
        ]));

        // Rol de Admin: Control casi total, excepto roles y permisos
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(array_merge($gerenteRole->permissions->pluck('name')->toArray(), [
            'ver-menu-administracion', 'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'eliminar-usuarios',
            'ver-menu-configuracion',
            'ver-sucursales', 'crear-sucursales', 'editar-sucursales', 'eliminar-sucursales',
            'ver-tipos-credito', 'crear-tipos-credito', 'editar-tipos-credito', 'eliminar-tipos-credito',
            'ver-tasas-interes', 'crear-tasas-interes', 'editar-tasas-interes', 'eliminar-tasas-interes',
        ]));

        // Rol Super-Admin: Acceso total a todo el sistema
        $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin']);
        $superAdminRole->givePermissionTo(Permission::all());


        // ------------------------------------------------------------------
        // CREACIÓN DE USUARIOS DE PRUEBA
        // ------------------------------------------------------------------
        User::firstOrCreate(
            ['email' => 'superadmin@credinos.com'],
            ['name' => 'Super Administrador', 'password' => Hash::make('password')]
        )->assignRole($superAdminRole);
    }
}