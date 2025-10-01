<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClienteController extends Controller
{
    /**
     * Muestra una lista paginada de todos los clientes.
     */
    public function index()
    {
        $clientes = Cliente::with('sucursal')->orderBy('apellido_paterno')->paginate(15);
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('clientes.create', compact('sucursales'));
    }

    /**
     * Guarda un nuevo cliente y sus referencias en la base de datos.
     */
    public function store(Request $request)
    {
        // Validamos los datos usando nuestro método centralizado de reglas
        $validatedData = $request->validate($this->getValidationRules());

        try {
            // Usamos una transacción para asegurar la integridad de los datos
            DB::beginTransaction();

            // Creamos el cliente con los datos principales
            $cliente = Cliente::create($validatedData);
            
            // Usamos la relación para crear las referencias asociadas
            $cliente->referencias()->createMany($validatedData['referencias']);

            DB::commit(); // Si todo salió bien, confirmamos los cambios

        } catch (\Exception $e) {
            DB::rollBack(); // Si algo falla, revertimos todo
            // Redirigimos de vuelta con un mensaje de error y los datos del formulario
            return back()->with('error', 'Ocurrió un error al registrar el cliente: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un cliente existente.
     */
    public function edit(Cliente $cliente)
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        // Cargamos las referencias para poder mostrarlas en el formulario
        $cliente->load('referencias'); 
        
        return view('clientes.edit', compact('cliente', 'sucursales'));
    }

    /**
     * Actualiza un cliente específico y sus referencias en la base de datos.
     */
    public function update(Request $request, Cliente $cliente)
    {
        // Validamos, pasando el ID del cliente para ignorarlo en las reglas 'unique'
        $validatedData = $request->validate($this->getValidationRules($cliente->id_cliente));

        try {
            DB::beginTransaction();

            $cliente->update($validatedData);
            
            // Actualizamos referencias: la forma más simple es borrarlas y crearlas de nuevo
            $cliente->referencias()->delete();
            $cliente->referencias()->createMany($validatedData['referencias']);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al actualizar el cliente: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Elimina (soft delete) un cliente de la base de datos.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }
    
    /**
     * Busca clientes para autocompletado (ej. Select2).
     */
    public function search(Request $request)
    {
        $term = $request->input('term', '');
        $clientes = Cliente::where('nombre', 'LIKE', '%' . $term . '%')
            ->orWhere('apellido_paterno', 'LIKE', '%' . $term . '%')
            ->orWhere('apellido_materno', 'LIKE', '%' . $term . '%')
            ->orWhere('id_cliente', $term)
            ->limit(10)
            ->get();

        $results = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id_cliente,
                // Usamos el accesor getNombreCompletoAttribute() del modelo Cliente
                'text' => $cliente->nombre_completo . ' (ID: ' . $cliente->id_cliente . ')'
            ];
        });

        return response()->json($results);
    }

    /**
     * Define y centraliza las reglas de validación para no repetir código.
     *
     * @param int|null $clienteId El ID del cliente a ignorar en reglas 'unique' (para updates).
     * @return array
     */
    private function getValidationRules(int $clienteId = null): array
    {
        // Reglas de unicidad que cambian si estamos editando
        $curpRule = 'nullable|string|max:18|unique:clientes,curp';
        $emailRule = 'nullable|email|max:255|unique:clientes,email';
        
        if ($clienteId) {
            $curpRule .= ',' . $clienteId . ',id_cliente';
            $emailRule .= ',' . $clienteId . ',id_cliente';
        }

        // Límites de edad (ej. entre 18 y 80 años)
        $minAgeDate = Carbon::now()->subYears(80)->toDateString();
        $maxAgeDate = Carbon::now()->subYears(18)->toDateString();

        // Límite para comprobante de domicilio (máximo 3 meses de antigüedad)
        $minProofDate = Carbon::now()->subMonths(3)->toDateString();
        $maxProofDate = Carbon::now()->toDateString(); // No puede ser una fecha futura

        return [
            // Sección 1: Datos Personales
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'fecha_nacimiento' => "required|date|after_or_equal:$minAgeDate|before_or_equal:$maxAgeDate",
            'genero' => 'required|string|in:Masculino,Femenino,Otro',
            'curp' => $curpRule,
            'vencimiento_ine' => 'required|date|after_or_equal:today',
            'estado_nacimiento' => 'required|string|max:100',
            'nacionalidad' => 'required|string|max:100',
            'estado_civil' => 'required|string|max:50',
            'numero_hijos' => 'required|integer|min:0',
            'dependientes_economicos' => 'required|integer|min:0',
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:50',
            'colonia' => 'required|string|max:255',
            'codigo_postal' => 'required|string|max:10',
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'fecha_comprobante_domicilio' => "required|date|between:$minProofDate,$maxProofDate",

            // Sección 2: Datos Laborales
            'nombre_negocio' => 'required|string|max:255',
            'giro_negocio' => 'required|string|max:255',
            'destino_credito' => 'required|string|max:255',
            'antiguedad_negocio' => 'required|integer|min:0',

            // Sección 3: Referencias (valida que el array exista y tenga exactamente 2 elementos)
            'referencias' => 'required|array|size:2',
            // Valida los campos dentro de cada elemento del array de referencias
            'referencias.*.nombre_referencia' => 'required|string|max:255',
            'referencias.*.parentesco' => 'required|string|max:100',
            'referencias.*.telefono' => 'required|string|max:20',
            
            // Asignación
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
        ];
    }
}