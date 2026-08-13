<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar; // <--- Importante añadir esto

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Resetear roles y permisos cacheados
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. CREACIÓN DE PERMISOS ORGANIZADOS POR GRUPO
        $permissionsByGroup = [
            'Dashboard' => [
                'ver-widget-contratos-vencer',
                'ver-widget-cumpleanos',
                'ver-widget-aniversarios',
                'ver-widget-nuevos-ingresos',
                'ver-widget-imss',
                'ver-widget-accesos-rapidos',
                'ver-widget-rentabilidad-sucursales',
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
                // 🔥 NUEVOS PERMISOS PARA NÓMINA FISCAL 🔥
                'ver-timbrado', 'ejecutar-timbrado', 'configurar-timbrado',
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
                'descargar-reporte-ejecutivo-ia',
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
                'ver-productos-credito', 'crear-productos-credito', 'editar-productos-credito', 'eliminar-productos-credito',
            ],
            'Módulo de Prueba' => [
                'ver-modulo-prueba',
                'usar-modulo-prueba',
            ],  
        ];

        // Crear los permisos en la base de datos (esto es seguro, usa firstOrCreate)
        foreach ($permissionsByGroup as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }
        }
        $this->command->info('Permisos creados o verificados.');

        // 3. --- CREACIÓN DE ROL Y USUARIO SUPER-ADMIN (SOLO ESTE) ---
        $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all()); // Asignar todos los permisos al Super-Admin
        $this->command->info('Rol Super-Admin creado y sincronizado con todos los permisos.');

        // 4. --- CREAR EL USUARIO SUPER-ADMIN SOLICITADO ---
        // Busca al usuario por el email
        $user = User::where('email', 'curiel@facturame.org')->first();

        if (!$user) {
            // Si no existe, lo crea con los datos que pediste
            User::create([
                'name' => 'Super Administrador',
                'email' => 'curiel@facturame.org',
                'password' => Hash::make('Carcur97#')
            ])->assignRole($superAdminRole);
            $this->command->info('Usuario Super-Admin (curiel@facturame.org) creado exitosamente.');
        } else {
            // Si ya existe, solo se asegura de que tenga el rol de Super-Admin
            $user->assignRole($superAdminRole);
            $this->command->info('Usuario Super-Admin (curiel@facturame.org) ya existía. Se le asignó el rol por si no lo tenía.');
        }
    }
}