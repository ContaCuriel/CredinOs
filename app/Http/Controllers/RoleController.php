<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        $permissions = Permission::orderBy('name')->get();
        return view('roles.create', compact('permissions'));
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
        
        // Limpiar la caché de permisos
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
                         ->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un rol existente.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
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

        // La línea clave que sincroniza los permisos desde el formulario de edición.
        $role->syncPermissions($request->input('permissions', []));

        // Limpiar la caché es crucial para que los cambios se reflejen inmediatamente.
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
                         ->with('success', 'Rol y permisos actualizados exitosamente.');
    }

    /**
     * Elimina un rol de la base de datos.
     */
    public function destroy(Role $role)
    {
        // Medida de seguridad para no eliminar el rol más importante.
        if ($role->name == 'Super-Admin') {
            return redirect()->route('roles.index')
                             ->with('error', 'No se puede eliminar el rol de Super Administrador.');
        }

        $role->delete();
        
        // Limpiar la caché de permisos
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
                         ->with('success', 'Rol eliminado exitosamente.');
    }
}