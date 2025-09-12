<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // <-- AÑADE ESTA LÍNEA PARA HASHEAR CONTRASEÑAS
use Illuminate\Validation\Rules;     // <-- AÑADE ESTA LÍNEA PARA REGLAS DE CONTRASEÑA DE BREEZE
use Illuminate\Support\Facades\Auth;
use App\Models\Sucursal;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los usuarios, ordenados por nombre
        // Usamos paginación por si tienes muchos
        $users = User::orderBy('name', 'asc')->paginate(10); // Muestra 10 por página

        // Pasamos la colección de usuarios a la vista
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    // Simplemente retornamos la vista del formulario de creación de usuarios
    return view('users.create');
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validación de los datos del formulario
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // Usa las reglas de contraseña de Breeze
        ],[
            // Mensajes de error personalizados (opcional)
            'name.required' => 'El nombre del usuario es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            // Los mensajes para Rules\Password::defaults() son manejados por Laravel
        ]);

        // 2. Creación del Usuario
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // Hashear la contraseña
            // 'email_verified_at' => now(), // Opcional: Marcar el email como verificado inmediatamente
        ]);

        // Aquí podrías añadir lógica para asignar un rol si ya tuvieras el sistema de roles.
        // Ejemplo: $user->assignRole('nombre_del_rol');

        // 3. Redirección con Mensaje de Éxito
        return redirect()->route('users.index')
                         ->with('success', '¡Usuario registrado exitosamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit(User $user)
    {
        // Cargamos todas las listas que necesitaremos en los dropdowns y checkboxes
        $roles = Role::all();
        $sucursales = Sucursal::all();

        // Obtenemos los roles que el usuario ya tiene para poder marcar los checkboxes
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('users.edit', compact('user', 'roles', 'sucursales', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, User $user)
    {
        // 1. Validamos los datos que vienen del formulario
        $request->validate([
            'roles' => 'nullable|array',
            'id_sucursal' => 'nullable|exists:sucursales,id_sucursal',
        ]);

        // 2. Actualizamos los permisos de sucursal
        $user->ver_todas_sucursales = $request->has('ver_todas_sucursales');
        // Si 'ver_todas_sucursales' está marcado, id_sucursal se guarda como null.
        $user->id_sucursal = $user->ver_todas_sucursales ? null : $request->id_sucursal;
        $user->save();

        // 3. Sincronizamos los roles
        // El método syncRoles del paquete Spatie se encarga de añadir/quitar los roles necesarios.
        $user->syncRoles($request->input('roles', []));

        // 4. Redirigimos con un mensaje de éxito
        return redirect()->route('users.index')
                         ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) // User $user es inyectado por Route-Model Binding
{
    // Impedir que un usuario se elimine a sí mismo
    if (Auth::id() == $user->id) {
        return redirect()->route('users.index')
                         ->with('error', 'No puedes eliminar tu propio usuario.');
    }

    // Podrías añadir lógica aquí si necesitas reasignar tareas
    // o manejar registros relacionados antes de eliminar al usuario.

    $userName = $user->name; // Guardar el nombre para el mensaje
    $user->delete(); // Elimina el usuario de la base de datos

    return redirect()->route('users.index')
                     ->with('success', '¡Usuario "' . $userName . '" eliminado exitosamente!');
}
}
