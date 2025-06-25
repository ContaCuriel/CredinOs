<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role; // <-- ¡Importante! Usamos el modelo Role del paquete

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los roles de la base de datos
        $roles = Role::all();

        // Devolvemos la vista, pasándole la lista de roles
        return view('roles.index', compact('roles'));
    }

     public function create()
    {
        // Obtenemos todos los permisos para poder listarlos en la vista
        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }
    
   public function store(Request $request)
{
    // 1. Validar los datos
    $request->validate([
        'name' => 'required|string|unique:roles,name',
        'permissions' => 'nullable|array',
        'permissions.*' => 'string|exists:permissions,name', // Validación mejorada
    ]);

    // 2. Crear el nuevo rol
    $role = Role::create(['name' => $request->name]);

    // 3. Asignar los permisos seleccionados
    if (!empty($request->permissions)) {
        // Ahora $request->permissions contiene los nombres, así que esto funcionará
        $role->syncPermissions($request->permissions);
    }

    // 4. Redirigir a la lista de roles con un mensaje de éxito
    return redirect()->route('roles.index')
                     ->with('success', 'Rol creado exitosamente.');
}

 public function edit(Role $role)
    {
        // Obtenemos todos los permisos para poder listarlos
        $permissions = Permission::all();

        // Obtenemos los permisos que este rol ya tiene asignados
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

      public function update(Request $request, Role $role)
    {
        // 1. Validar los datos
        // La regla 'unique' necesita ignorar el rol actual, por eso se añade el ID del rol al final.
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // 2. Actualizar el nombre del rol
        $role->update(['name' => $request->name]);

        // 3. Sincronizar los permisos
        $role->syncPermissions($request->input('permissions', []));

        // 4. Redirigir con mensaje de éxito
        return redirect()->route('roles.index')
                         ->with('success', 'Rol actualizado exitosamente.');
    }
    
     public function destroy(Role $role)
    {
        // Añadimos una capa extra de seguridad. Si alguien intenta borrar el rol Super-Admin
        // modificando la URL, esta validación lo detendrá.
        if ($role->name == 'Super-Admin') {
            return redirect()->route('roles.index')
                             ->with('error', 'No se puede eliminar el rol de Super Administrador.');
        }

        $role->delete();

        return redirect()->route('roles.index')
                         ->with('success', 'Rol eliminado exitosamente.');
    }

    // ... aquí irán las otras funciones (create, store, etc.) más adelante ...
}