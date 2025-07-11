<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Muestra una lista de todos los grupos.
     */
     public function index()
    {
        // Esta línea busca los grupos y carga la información de su sucursal y asesor
        // para evitar errores en la vista.
        $groups = Group::with('sucursal', 'asesor')->orderBy('nombre_grupo')->paginate(15);
        
        return view('groups.index', compact('groups'));
    }


    /**
     * Muestra el formulario para crear un nuevo grupo.
     */
    public function create()
    {
        // Pasamos sucursales y usuarios (asesores) para los campos de selección
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $asesores = User::orderBy('name')->get(); // Asumiendo que los asesores son usuarios
        return view('groups.create', compact('sucursales', 'asesores'));
    }

    /**
     * Guarda un nuevo grupo en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre_grupo' => 'required|string|max:255|unique:groups,nombre_grupo',
            'id_sucursal'  => 'required|exists:sucursales,id_sucursal',
            'id_asesor'    => 'required|exists:users,id',
        ]);

        Group::create($validatedData);

        return redirect()->route('groups.index')->with('success', 'Grupo creado exitosamente.');
    }

    /**
     * Muestra la página para ver y administrar los miembros de un grupo.
     */
    public function show(Group $group)
    {
        // Cargamos los clientes que ya están en el grupo
        $group->load('clients');

        // Obtenemos una lista de clientes que NO están en este grupo para poder añadirlos
        $clientsToAdd = Cliente::whereDoesntHave('groups', function ($query) use ($group) {
            $query->where('group_id', $group->id_group);
        })->get();
        
        return view('groups.show', compact('group', 'clientsToAdd'));
    }

    /**
     * Muestra el formulario para editar un grupo.
     */
    public function edit(Group $group)
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $asesores = User::orderBy('name')->get();
        return view('groups.edit', compact('group', 'sucursales', 'asesores'));
    }

    /**
     * Actualiza un grupo existente.
     */
    public function update(Request $request, Group $group)
    {
        $validatedData = $request->validate([
            'nombre_grupo' => 'required|string|max:255|unique:groups,nombre_grupo,' . $group->id_group . ',id_group',
            'id_sucursal'  => 'required|exists:sucursales,id_sucursal',
            'id_asesor'    => 'required|exists:users,id',
            'status'       => 'required|in:Activo,Inactivo,Completado',
        ]);

        $group->update($validatedData);

        return redirect()->route('groups.index')->with('success', 'Grupo actualizado exitosamente.');
    }

    /**
     * Elimina un grupo.
     */
    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Grupo eliminado exitosamente.');
    }

    /**
     * Añade un miembro a un grupo.
     */
    public function addMember(Request $request, Group $group)
    {
        $request->validate(['client_id' => 'required|exists:clientes,id_cliente']);

        // El método attach() añade la relación en la tabla pivote
        $group->clients()->attach($request->client_id);

        return redirect()->route('groups.show', $group->id_group)->with('success', 'Miembro añadido al grupo.');
    }

    /**
     * Quita un miembro de un grupo.
     */
    public function removeMember(Request $request, Group $group, Cliente $client)
    {
        // El método detach() elimina la relación
        $group->clients()->detach($client->id_cliente);

        return redirect()->route('groups.show', $group->id_group)->with('success', 'Miembro eliminado del grupo.');
    }

    public function getMembers(Group $group)
{
    // Cargamos la relación 'clientes' que definiste en tu modelo Group
    $members = $group->clientes()->get();

    // Devolvemos los miembros en formato JSON
    return response()->json($members);
}
}