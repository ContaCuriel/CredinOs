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
        // Agregamos 'garantia' al final de esta lista
        $credito = Credito::with(['producto', 'asesor', 'cliente', 'grupo', 'integrantes', 'cuentasDesembolso', 'garantia'])->findOrFail($id);
        
        $patrones = \App\Models\Patron::orderBy('nombre_comercial')->get();
        return view('creditos.show', compact('credito', 'patrones'));
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
            'sucursal_id'      => 'required|exists:sucursales,id_sucursal',
            'producto_id'      => 'required|exists:productos_credito,id',
            'asesor_id'        => 'required|exists:empleados,id_empleado',
            'monto_solicitado' => 'required|numeric|min:1',
            'nombre_credito'   => 'nullable|string|max:255',
            'nombre_grupo'     => 'nullable|string|max:255', 
            'clientes'         => 'required|array|min:1',
            'clientes.*.id'    => 'required|exists:clientes,id_cliente',
            'clientes.*.monto' => 'required|numeric|min:0',
            'lider_id'         => 'nullable|exists:clientes,id_cliente',
            'cuentas'             => 'required|array|min:1',
            'cuentas.*.banco'     => 'required|string|max:100',
            'cuentas.*.titular'   => 'required|string|max:255',
            'cuentas.*.cuenta'    => 'required|string|max:50',
            // Validaciones de la garantía (es un array que puede o no venir)
            'garantia'         => 'nullable|array',
            'garantia.tipo'    => 'nullable|in:vehiculo,propiedad',
        ]);

        try {
            DB::beginTransaction();

            $producto = ProductoCredito::findOrFail($validated['producto_id']);
            
            $grupo_id = null;
            $cliente_id_individual = null;

            $nombre_grupo = $validated['nombre_grupo'] ?? null;
            $nombre_credito = $validated['nombre_credito'] ?? null;
            $lider_id_seleccionado = $validated['lider_id'] ?? null;

            // 2. ¿ES GRUPAL O INDIVIDUAL?
            if ($producto->tipo_credito == 'grupal') {
                if (empty($nombre_grupo)) {
                    throw new \Exception("El nombre del grupo es obligatorio para este tipo de producto.");
                }
                $grupo = Grupo::create(['nombre_grupo' => $nombre_grupo]);
                $grupo_id = $grupo->id;
            } else {
                $cliente_id_individual = $validated['clientes'][0]['id'];
            }

            // 3. CREAMOS EL CRÉDITO
            $credito = Credito::create([
                'folio' => 'CR-' . strtoupper(uniqid()), 
                'nombre_credito' => $nombre_credito,
                'sucursal_id' => $validated['sucursal_id'],
                'cliente_id' => $cliente_id_individual,
                'grupo_id' => $grupo_id,
                'producto_id' => $producto->id,
                'monto_solicitado' => $validated['monto_solicitado'],
                'plazo_solicitado' => $producto->plazo_maximo, 
                'tasa_interes_aplicada' => $producto->tasa_interes,
                'comision_apertura_aplicada' => $producto->cobro_comision_apertura,
                'estatus' => 'solicitado',
                'fecha_solicitud' => now(),
                'asesor_id' => $validated['asesor_id'],
            ]);

            // 4. ATAMOS A LOS CLIENTES
            $syncData = [];
            foreach ($validated['clientes'] as $cliente) {
                $es_lider = ($lider_id_seleccionado == $cliente['id']) ? true : false;
                if ($producto->tipo_credito == 'individual') {
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


            // 6. GUARDAMOS LA GARANTÍA (SI APLICA)
            if ($producto->requiere_garantia && !empty($request->input('garantia'))) {
                // Aquí tomamos los datos directos del request para que Laravel no los borre
                $garantiaData = $request->input('garantia');
                
                $credito->garantia()->create([
                    'tipo_garantia' => $garantiaData['tipo'],
                    'vehiculo_documento' => $garantiaData['vehiculo_documento'] ?? null,
                    'vehiculo_tipo' => $garantiaData['vehiculo_tipo'] ?? null,
                    'vehiculo_marca' => $garantiaData['vehiculo_marca'] ?? null,
                    'vehiculo_modelo' => $garantiaData['vehiculo_modelo'] ?? null,
                    'vehiculo_anio' => $garantiaData['vehiculo_anio'] ?? null,
                    'vehiculo_motor' => $garantiaData['vehiculo_motor'] ?? null,
                    'vehiculo_color' => $garantiaData['vehiculo_color'] ?? null,
                    'vehiculo_serie' => $garantiaData['vehiculo_serie'] ?? null,
                    'propiedad_documento' => $garantiaData['propiedad_documento'] ?? null,
                    'propiedad_ubicacion' => $garantiaData['propiedad_ubicacion'] ?? null,
                    'propiedad_medidas' => $garantiaData['propiedad_medidas'] ?? null,
                    'propiedad_superficie' => $garantiaData['propiedad_superficie'] ?? null,
                    // Estos empiezan por defecto:
                    'estatus_resguardo' => 'En Bóveda Sucursal',
                ]);
            }

            DB::commit();
            return redirect()->route('creditos.index')->with('success', '¡Solicitud de crédito creada y enviada a autorización exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
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

    public function destroy($id)
    {
        try {
            $credito = Credito::findOrFail($id);
            
            // Solo se pueden borrar si no han sido aprobados ni desembolsados
            if ($credito->estatus != 'solicitado') {
                return back()->with('error', 'No puedes eliminar un crédito que ya fue procesado.');
            }

            DB::beginTransaction();
            
            // Limpiamos las tablas relacionadas
            $credito->integrantes()->detach(); // Quita a los clientes de la tabla pivote
            $credito->cuentasDesembolso()->delete(); // Borra las cuentas
            if($credito->garantia) {
                $credito->garantia()->delete(); // Borra la garantía
            }
            
            // Borramos el crédito
            $credito->delete();
            
            DB::commit();
            return redirect()->route('creditos.index')->with('success', '¡Solicitud eliminada y limpiada correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}