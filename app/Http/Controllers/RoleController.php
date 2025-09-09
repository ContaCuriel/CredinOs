<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Database\Seeders\PermissionSeeder; // Importamos el Seeder

class RoleController extends Controller
{
    /**
     * Muestra una lista de todos los roles.
     */
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo rol.
     */
    public function create()
    {
        // Para el 'create', la lógica puede ser similar a la de 'edit' para mantener la consistencia.
        $permissions = Permission::orderBy('name')->get();
        $permissionsByGroup = $this->getGroupedPermissions(); // Reutilizamos la lógica de agrupación

        return view('roles.create', compact('permissions', 'permissionsByGroup'));
    }

    /**
     * Guarda un nuevo rol en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        $role->syncPermissions($request->input('permissions', []));
        
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
                         ->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un rol existente.
     */
    public function edit(Role $role): View
{
    // Usamos la fachada DB para una consulta SQL cruda
    $totalPermissions = \Illuminate\Support\Facades\DB::table('permissions')->count();

    // Buscamos específicamente uno de los nuevos permisos
    $renunciaPermission = \Illuminate\Support\Facades\DB::table('permissions')->where('name', 'like', '%renuncia%')->get();
    $pruebaPermission = \Illuminate\Support\Facades\DB::table('permissions')->where('name', 'like', '%prueba%')->get();

    dd([
        'CONTEXTO' => 'Petición WEB',
        'Total de permisos contados en la tabla' => $totalPermissions,
        'Permiso "Renuncia" encontrado' => $renunciaPermission,
        'Permiso "Prueba" encontrado' => $pruebaPermission,
        'Configuración de Conexión Activa' => config('database.connections.' . config('database.default'))
    ]);
}
    
    /**
     * Helper privado para obtener los permisos agrupados.
     */
    private function getGroupedPermissions()
    {
        // Obtenemos la estructura de permisos del Seeder.
        $seeder = new PermissionSeeder();
        // Necesitamos acceder a la propiedad, pero como no es pública, la replicamos aquí.
        // O podrías hacerla pública/estática en el Seeder. Por ahora, esto es más simple.
        $permissionsStructure = [
            'Dashboard' => ['ver-widget-contratos-vencer', 'ver-widget-cumpleanos', 'ver-widget-aniversarios', 'ver-widget-nuevos-ingresos', 'ver-widget-imss', 'ver-widget-accesos-rapidos'],
            'Menús Principales' => ['ver-menu-creditos', 'ver-menu-rh', 'ver-menu-contabilidad', 'ver-menu-administracion', 'ver-menu-configuracion'],
            'Créditos y Cobranza' => ['ver-clientes', 'crear-clientes', 'editar-clientes', 'eliminar-clientes', 'ver-grupos', 'crear-grupos', 'editar-grupos', 'eliminar-grupos', 'ver-creditos', 'registrar-credito', 'editar-credito', 'eliminar-credito', 'aprobar-credito', 'desembolsar-credito'],
            'Recursos Humanos' => ['ver-empleados', 'crear-empleados', 'editar-empleados', 'eliminar-empleados', 'ver-contratos', 'crear-contratos', 'imprimir-contratos', 'exportar-contratos', 'ver-asistencias', 'registrar-asistencias', 'editar-asistencias', 'ver-vacaciones', 'registrar-vacaciones', 'ver-deducciones', 'crear-deducciones', 'editar-deducciones', 'eliminar-deducciones', 'ver-lista-raya', 'exportar-lista-raya', 'ver-finiquitos', 'calcular-finiquitos', 'exportar-finiquitos', 'ver-gestion-imss', 'tramitar-imss', 'ver-aguinaldo', 'calcular-aguinaldo', 'exportar-aguinaldo', 'ver-renuncias', 'generar-renuncias'],
            'Contabilidad' => ['ver-gastos', 'crear-gastos', 'editar-gastos', 'eliminar-gastos', 'aprobar-gastos', 'ver-reportes', 'ver-cuentas', 'crear-cuentas', 'editar-cuentas', 'eliminar-cuentas', 'ver-polizas', 'ver-detalle-polizas', 'ver-colocaciones', 'ver-recuperaciones'],
            'Administración' => ['ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'eliminar-usuarios', 'ver-roles', 'crear-roles', 'editar-roles', 'eliminar-roles'],
            'Configuración del Sistema' => ['ver-sucursales', 'crear-sucursales', 'editar-sucursales', 'eliminar-sucursales', 'ver-puestos', 'crear-puestos', 'editar-puestos', 'eliminar-puestos', 'ver-patrones', 'crear-patrones', 'editar-patrones', 'eliminar-patrones', 'ver-horarios', 'crear-horarios', 'editar-horarios', 'eliminar-horarios', 'ver-categorias', 'crear-categorias', 'editar-categorias', 'eliminar-categorias', 'ver-tipos-credito', 'crear-tipos-credito', 'editar-tipos-credito', 'eliminar-tipos-credito', 'ver-tasas-interes', 'crear-tasas-interes', 'editar-tasas-interes', 'eliminar-tasas-interes'],
        ];

        $permissions = Permission::orderBy('name')->get()->keyBy('name');
        $grouped = [];

        foreach ($permissionsStructure as $groupName => $permissionNames) {
            $grouped[$groupName] = [];
            foreach ($permissionNames as $name) {
                if (isset($permissions[$name])) {
                    $grouped[$groupName][] = $permissions[$name];
                }
            }
        }
        return $grouped;
    }

    /**
     * Actualiza un rol existente en la base de datos.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions', []));

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
                         ->with('success', 'Rol y permisos actualizados exitosamente.');
    }

    /**
     * Elimina un rol de la base de datos.
     */
    public function destroy(Role $role)
    {
        if ($role->name == 'Super-Admin') {
            return redirect()->route('roles.index')
                             ->with('error', 'No se puede eliminar el rol de Super Administrador.');
        }

        $role->delete();
        
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
                         ->with('success', 'Rol eliminado exitosamente.');
    }
}
