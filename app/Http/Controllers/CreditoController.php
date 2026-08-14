<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\ProductoCredito;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sucursal;

class CreditoController extends Controller
{
    public function index()
    {
        // Traemos los créditos con sus relaciones para no saturar la base de datos
        $creditos = Credito::with(['producto', 'asesor', 'cliente', 'grupo', 'integrantes'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);
                            
        return view('creditos.index', compact('creditos'));
    }

    public function show($id)
    {
        // Traemos el crédito con todas sus relaciones para armar la vista completa
        $credito = Credito::with(['producto', 'asesor', 'cliente', 'grupo', 'integrantes', 'cuentasDesembolso'])->findOrFail($id);
        
        return view('creditos.show', compact('credito'));
    }

    public function create()
    {
        $productos = ProductoCredito::where('activo', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get(); // <-- NUEVO
        
        $asesores = Empleado::with('sucursal')
            ->whereHas('puesto', function ($query) {
                $query->where('nombre_puesto', 'ILIKE', 'ASESOR%')
                      ->orWhere('nombre_puesto', 'ILIKE', 'GERENTE%');
            })
            ->whereIn('status', ['Alta', 'ALTA', 'alta', 'Activo', 'ACTIVO'])
            ->orderBy('nombre_completo')->get();

        // Manda las sucursales en el compact
        return view('creditos.create', compact('productos', 'asesores', 'sucursales')); 
    }

    public function store(Request $request)
    {
        // 1. VALIDACIÓN MAESTRA
        $validated = $request->validate([
            'producto_id'      => 'required|exists:productos_credito,id',
            'asesor_id'        => 'required|exists:empleados,id_empleado',
            'monto_solicitado' => 'required|numeric|min:1',
            'sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'nombre_credito'   => 'nullable|string|max:255',
            'nombre_grupo'     => 'nullable|string|max:255', // Solo si es grupal
            
            // Arreglo de clientes y cuentas (Valida que venga al menos 1 de cada uno)
            'clientes'         => 'required|array|min:1',
            'clientes.*.id'    => 'required|exists:clientes,id_cliente',
            'clientes.*.monto' => 'required|numeric|min:0',
            'lider_id'         => 'nullable|exists:clientes,id_cliente', // Quién es el líder
            
            'cuentas'             => 'required|array|min:1',
            'cuentas.*.banco'     => 'required|string|max:100',
            'cuentas.*.titular'   => 'required|string|max:255',
            'cuentas.*.cuenta'    => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $producto = ProductoCredito::findOrFail($validated['producto_id']);
            
            $grupo_id = null;
            $cliente_id_individual = null;

            // 2. ¿ES GRUPAL O INDIVIDUAL?
            if ($producto->tipo_credito == 'grupal') {
                if (empty($validated['nombre_grupo'])) {
                    throw new \Exception("El nombre del grupo es obligatorio para este tipo de producto.");
                }
                // Creamos el Grupo
                $grupo = Grupo::create(['nombre_grupo' => $validated['nombre_grupo']]);
                $grupo_id = $grupo->id;
            } else {
                // Si es individual, el cliente principal es el primero de la lista
                $cliente_id_individual = $validated['clientes'][0]['id'];
            }

            // 3. CREAMOS EL CRÉDITO (La Cabecera)
            $credito = Credito::create([
                'folio' => 'CR-' . strtoupper(uniqid()), // Folio temporal autogenerado
                'nombre_credito' => $validated['nombre_credito'],
                'sucursal_id' => $validated['sucursal_id'],
                'cliente_id' => $cliente_id_individual,
                'grupo_id' => $grupo_id,
                'producto_id' => $producto->id,
                'monto_solicitado' => $validated['monto_solicitado'],
                'plazo_solicitado' => $producto->plazo_maximo, // Toma el plazo del producto por defecto en la solicitud
                
                // La "fotografía" financiera actual
                'tasa_interes_aplicada' => $producto->tasa_interes,
                'comision_apertura_aplicada' => $producto->cobro_comision_apertura,
                
                'estatus' => 'solicitado',
                'fecha_solicitud' => now(),
                'asesor_id' => $validated['asesor_id'],
            ]);

            // 4. ATAMOS A LOS CLIENTES (Integrantes y Líder)
            $syncData = [];
            foreach ($validated['clientes'] as $cliente) {
                $es_lider = ($validated['lider_id'] == $cliente['id']) ? true : false;
                
                // Si es individual y solo hay uno, él es el líder por defecto
                if ($producto->tipo_credito == 'individual' && count($validated['clientes']) == 1) {
                    $es_lider = true;
                }

                $syncData[$cliente['id']] = [
                    'es_lider' => $es_lider,
                    'monto_individual' => $cliente['monto']
                ];
            }
            $credito->integrantes()->sync($syncData);

            // 5. GUARDAMOS LAS CUENTAS BANCARIAS
            foreach ($validated['cuentas'] as $cuenta) {
                $credito->cuentasDesembolso()->create([
                    'banco' => $cuenta['banco'],
                    'titular' => $cuenta['titular'],
                    'numero_cuenta' => $cuenta['cuenta'],
                ]);
            }

            DB::commit();
            return redirect()->route('creditos.index')->with('success', '¡Solicitud de crédito creada y enviada a autorización exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage())->withInput();
        }
    }

    public function search(Request $request)
    {
        $term = $request->term;
        
        if (!$term) {
            return response()->json([]);
        }

        // Buscamos coincidencias en nombre o apellidos
        $clientes = Cliente::where('nombre', 'ILIKE', "%$term%")
                    ->orWhere('apellido_paterno', 'ILIKE', "%$term%")
                    ->orWhere('apellido_materno', 'ILIKE', "%$term%")
                    ->take(15)
                    ->get();

        $results = [];
        foreach ($clientes as $cliente) {
            $results[] = [
                'id' => $cliente->id_cliente, // Asegúrate de que sea tu llave primaria correcta
                'text' => trim($cliente->nombre . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)
            ];
        }

        return response()->json($results);
    }
}