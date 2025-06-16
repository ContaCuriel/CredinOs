<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // <-- AÑADE ESTA LÍNEA PARA HASHEAR CONTRASEÑAS
use Illuminate\Validation\Rules;     // <-- AÑADE ESTA LÍNEA PARA REGLAS DE CONTRASEÑA DE BREEZE
use Illuminate\Support\Facades\Auth;

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
    public function edit(User $user) // Laravel inyecta el modelo User
{
    // Pasamos el usuario a editar a la vista.
    // Más adelante, si tenemos roles, también podríamos pasar la lista de roles aquí.
    return view('users.edit', compact('user'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user) // User $user es inyectado por Route-Model Binding
{
    // 1. Validación de los datos del formulario
    $validatedData = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        // La regla 'unique' para email necesita ignorar el usuario actual que se está editando
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
        // La contraseña es opcional en la actualización. Si se proporciona, debe ser confirmada y cumplir las reglas.
        'password' => ['nullable', 'confirmed', Rules\Password::defaults()], 
    ],[
        // Mensajes de error personalizados (opcional)
        'name.required' => 'El nombre del usuario es obligatorio.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'El formato del correo electrónico no es válido.',
        'email.unique' => 'Este correo electrónico ya está registrado para otro usuario.',
        'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
    ]);

    // 2. Actualización de los datos básicos del Usuario
    $user->name = $validatedData['name'];
    $user->email = $validatedData['email'];

    // 3. Actualizar la contraseña SOLO SI se proporcionó una nueva
    if (!empty($validatedData['password'])) {
        $user->password = Hash::make($validatedData['password']);
    }

    // Si el email fue cambiado y tu sistema usa verificación de email,
    // podrías querer marcar el email como no verificado de nuevo:
    // if ($user->isDirty('email')) {
    //     $user->email_verified_at = null;
    // }

    $user->save(); // Guarda los cambios en la base de datos

    // 4. Redirección con Mensaje de Éxito
    return redirect()->route('users.index')
                     ->with('success', '¡Usuario "' . $user->name . '" actualizado exitosamente!');
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
