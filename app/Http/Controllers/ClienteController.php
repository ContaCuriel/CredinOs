<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Muestra una lista paginada de todos los clientes.
     */
    public function index()
    {
        // Obtenemos los clientes, 15 por página, ordenados por apellido
        $clientes = Cliente::with('sucursal')->orderBy('apellido_paterno')->paginate(15);
        
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create()
    {
        // Necesitamos la lista de sucursales para poder asignarla en el formulario
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('clientes.create', compact('sucursales'));
    }

    /**
     * Guarda un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        // Validamos los datos del formulario de creación
        $validatedData = $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'curp'             => 'nullable|string|max:18|unique:clientes,curp',
            'rfc'              => 'nullable|string|max:13|unique:clientes,rfc',
            'telefono_celular' => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255|unique:clientes,email',
            'calle'            => 'nullable|string|max:255',
            'numero'           => 'nullable|string|max:50',
            'colonia'          => 'nullable|string|max:255',
            'codigo_postal'    => 'nullable|string|max:10',
            'municipio'        => 'nullable|string|max:255',
            'estado'           => 'nullable|string|max:255',
            'id_sucursal'      => 'required|exists:sucursales,id_sucursal',
        'ocupacion'          => 'nullable|string|max:255',
'nombre_negocio'     => 'nullable|string|max:255',
'giro_negocio'       => 'nullable|string|max:255',
'antiguedad_negocio' => 'nullable|integer',
'ingresos_mensuales' => 'nullable|numeric|min:0',
'gastos_mensuales'   => 'nullable|numeric|min:0',

        ]);

        // Creamos el cliente con los datos validados
        Cliente::create($validatedData);

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente registrado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un cliente existente.
     */
    public function edit(Cliente $cliente)
    {
        // Obtenemos las sucursales para el campo de selección
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        // Pasamos el cliente a editar y la lista de sucursales a la vista
        return view('clientes.edit', compact('cliente', 'sucursales'));
    }

    /**
     * Actualiza un cliente específico en la base de datos.
     */
    public function update(Request $request, Cliente $cliente)
    {
        // Validamos los datos del formulario de edición
        $validatedData = $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            // La regla 'unique' debe ignorar el registro actual
            'curp'             => 'nullable|string|max:18|unique:clientes,curp,' . $cliente->id_cliente . ',id_cliente',
            'rfc'              => 'nullable|string|max:13|unique:clientes,rfc,' . $cliente->id_cliente . ',id_cliente',
            'telefono_celular' => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255|unique:clientes,email,' . $cliente->id_cliente . ',id_cliente',
            'calle'            => 'nullable|string|max:255',
            'numero'           => 'nullable|string|max:50',
            'colonia'          => 'nullable|string|max:255',
            'codigo_postal'    => 'nullable|string|max:10',
            'municipio'        => 'nullable|string|max:255',
            'estado'           => 'nullable|string|max:255',
            'id_sucursal'      => 'required|exists:sucursales,id_sucursal',
        'ocupacion'          => 'nullable|string|max:255',
'nombre_negocio'     => 'nullable|string|max:255',
'giro_negocio'       => 'nullable|string|max:255',
'antiguedad_negocio' => 'nullable|integer',
'ingresos_mensuales' => 'nullable|numeric|min:0',
'gastos_mensuales'   => 'nullable|numeric|min:0',

        ]);

        // Actualizamos el cliente
        $cliente->update($validatedData);

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Elimina (soft delete) un cliente de la base de datos.
     */
    public function destroy(Cliente $cliente)
    {
        // Realizamos el borrado lógico del cliente
        $cliente->delete();

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente eliminado exitosamente.');
    }
}