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
        // Tuvimos que añadir el trait UsesTenantConnection a Cliente, 
        // así que esta consulta ya funciona en el contexto del tenant.
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
     * Reglas de validación compartidas para no repetir código.
     */
    private function getValidationRules($clienteId = null): array
    {
        // Reglas de unicidad que cambian si estamos editando
        $curpRule = 'nullable|string|max:18|unique:clientes,curp';
        $emailRule = 'nullable|email|max:255|unique:clientes,email';
        if ($clienteId) {
            $curpRule .= ',' . $clienteId . ',id_cliente';
            $emailRule .= ',' . $clienteId . ',id_cliente';
        }

        // Límites de edad (ej. entre 18 y 80 años)
        $minDate = Carbon::now()->subYears(80)->toDateString();
        $maxDate = Carbon::now()->subYears(18)->toDateString();
        $currentYear = date('Y');

        // Límite para comprobante de domicilio (3 meses)
        $maxProofDate = Carbon::now()->toDateString();
        $minProofDate = Carbon::now()->subMonths(3)->toDateString();

        return [
            // Sección 1: Datos Personales
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255', // Ya no es obligatorio
            'fecha_nacimiento' => "required|date|after_or_equal:$minDate|before_or_equal:$maxDate",
            'curp' => $curpRule,
            'vencimiento_ine' => "required|integer|digits:4|gte:$currentYear", // Validar año
            'estado_civil' => 'required|string|in:Soltero(a),Casado(a),Divorciado(a),Viudo(a),Unión Libre', // Lista
            'telefono_celular' => 'required|string|max:20',
            'telefono_fijo' => 'nullable|string|max:20', // Nuevo campo
            
            // Dirección
            'codigo_postal' => 'required|string|max:10',
            'colonia' => 'required|string|max:255',
            'fecha_comprobante_domicilio' => "required|date|between:$minProofDate,$maxProofDate",
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:50',
            'anios_domicilio' => 'required|integer|min:0', // Nuevo campo
            'tipo_vivienda' => 'required|string|in:Propia,Rentada,Familiar,Hipotecada', // Nuevo campo (lista)

            // Sección 2: Datos Laborales
            'nombre_negocio' => 'required|string|max:255',
            'giro_negocio' => 'required|string|in:Comercio,Servicios,Industria,Agropecuario,Otro', // Lista
            'destino_credito' => 'required|string|in:Capital de Trabajo,Activo Fijo,Inversión,Otro', // Lista
            'antiguedad_negocio' => 'required|integer|min:0',

            // Sección 3: Referencias
            'referencias' => 'required|array|size:2',
            'referencias.*.nombre_referencia' => 'required|string|max:255',
            'referencias.*.parentesco' => 'required|string|max:100',
            'referencias.*.telefono' => 'required|string|max:20',
            
        ];
    }
    
    /**
     * Guarda un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {

        dd($request->all());

        $validatedData = $request->validate($this->getValidationRules());

        try {
            DB::beginTransaction();

            $cliente = Cliente::create($validatedData);
            
            // Guardar las referencias
            $cliente->referencias()->createMany($validatedData['referencias']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
     * Actualiza un cliente específico en la base de datos.
     */
    public function update(Request $request, Cliente $cliente)
    {

        dd($request->all());

        $validatedData = $request->validate($this->getValidationRules($cliente->id_cliente));


        try {
            DB::beginTransaction();

            $cliente->update($validatedData);
            
            // Actualizar referencias: borrarlas y crearlas de nuevo
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
                'text' => $cliente->nombre_completo . ' (ID: ' . $cliente->id_cliente . ')'
            ];
        });

        return response()->json($results);
    }
}