<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Auth;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GastoController extends Controller
{
    /**
     * Muestra la lista de gastos con filtros.
     */
    public function index(Request $request)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $query = Gasto::with(['sucursal', 'categoria', 'proveedor', 'usuario']);

        // ... (toda tu lógica de filtros, que está perfecta, se mantiene igual)
        if ($request->filled('search_term')) {
            $searchTerm = $request->input('search_term');
            $query->where(function($q) use ($searchTerm) {
                $q->where('descripcion', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('proveedor', function($q_proveedor) use ($searchTerm) {
                      $q_proveedor->where('nombre', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->input('sucursal_id'));
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->input('categoria_id'));
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_gasto', [$request->input('fecha_inicio'), $request->input('fecha_fin')]);
        }

        $gastos = $query->latest('fecha_gasto')->paginate(15);
        
        return view('gastos.index', compact('gastos', 'categorias', 'sucursales'));
    }

    /**
     * Muestra el formulario para crear un nuevo gasto.
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $sucursalUsuario = Auth::user()->sucursal;
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('gastos.create', [
            'categorias' => $categorias,
            'proveedores' => $proveedores,
            'sucursalUsuario' => $sucursalUsuario,
            'sucursales' => $sucursales,
        ]);
    }

    /**
     * Guarda un nuevo gasto en la base de datos.
     */
    public function store(Request $request)
    {
        Log::info('GastoController@store: Iniciando registro de gasto.');
        
        try {
            $validatedData = $request->validate([
                'fecha_gasto' => 'required|date',
                'sucursal_id' => 'required|exists:sucursales,id_sucursal',
                'proveedor_nombre' => 'required|string|max:255',
                'categoria_id' => 'required|exists:categorias,id',
                'descripcion' => 'nullable|string',
                'monto_subtotal' => 'required|numeric|min:0',
                'monto_iva' => 'nullable|numeric|min:0',
                'requiere_aprobacion' => 'nullable|boolean',
                'comprobante' => 'nullable|file|mimes:jpg,png,pdf,xml|max:2048'
            ]);
            Log::info('GastoController@store: Validación completada.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('GastoController@store: Error de validación.', ['errors' => $e->errors()]);
            throw $e;
        }

        DB::beginTransaction();
        Log::info('GastoController@store: Transacción iniciada.');

        try {
            $proveedor = Proveedor::firstOrCreate(['nombre' => $validatedData['proveedor_nombre']]);
            Log::info('GastoController@store: Proveedor manejado (ID: ' . $proveedor->id . ').');

            $nombreArchivo = null;
            if ($request->hasFile('comprobante')) {
                $archivo = $request->file('comprobante');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $archivo->storeAs('public/comprobantes', $nombreArchivo);
                Log::info('GastoController@store: Archivo subido: ' . $nombreArchivo);
            }

            $subtotal = $validatedData['monto_subtotal'];
            $iva = isset($validatedData['monto_iva']) ? $validatedData['monto_iva'] : 0;
            
            $requiereAprobacion = $request->has('requiere_aprobacion');
            $estado = $requiereAprobacion ? 'En Aprobación' : 'Aprobado';

            $dataToCreate = [
                'fecha_gasto' => $validatedData['fecha_gasto'],
                'sucursal_id' => $validatedData['sucursal_id'],
                'proveedor_id' => $proveedor->id,
                'categoria_id' => $validatedData['categoria_id'],
                // ===== CORRECCIÓN CLAVE =====
                // Cambiamos 'user_id' por el nombre correcto de la columna en tu BD.
                'usuario_registra_id' => Auth::id(),
                'descripcion' => $validatedData['descripcion'],
                'monto_subtotal' => $subtotal,
                'monto_iva' => $iva,
                'monto_total' => $subtotal + $iva,
                'nombre_archivo_comprobante' => $nombreArchivo,
                'requiere_aprobacion' => $requiereAprobacion,
                'estado' => $estado,
            ];

            Log::info('GastoController@store: Datos listos para crear el gasto.', $dataToCreate);
            
            Gasto::create($dataToCreate);
            
            Log::info('GastoController@store: Gasto creado en la BD.');

            DB::commit();
            Log::info('GastoController@store: Transacción confirmada (commit).');

            return redirect()->route('gastos.index')->with('success', 'Gasto registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GastoController@store: EXCEPCIÓN. Transacción revertida.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Hubo un error al registrar el gasto: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra el formulario para editar un gasto.
     */
    public function edit(Gasto $gasto)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('gastos.edit', compact('gasto', 'categorias', 'proveedores', 'sucursales'));
    }

    /**
     * Actualiza un gasto existente.
     */
    public function update(Request $request, Gasto $gasto)
    {
        $validatedData = $request->validate([
            'fecha_gasto' => 'required|date',
            'sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'proveedor_nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'descripcion' => 'nullable|string',
            'monto_subtotal' => 'required|numeric|min:0',
            'monto_iva' => 'nullable|numeric|min:0',
            'requiere_aprobacion' => 'nullable|boolean',
            'comprobante' => 'nullable|file|mimes:jpg,png,pdf,xml|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $proveedor = Proveedor::firstOrCreate(['nombre' => $validatedData['proveedor_nombre']]);
            
            $nombreArchivo = $gasto->nombre_archivo_comprobante;
            if ($request->hasFile('comprobante')) {
                if ($nombreArchivo) {
                    Storage::delete('public/comprobantes/' . $nombreArchivo);
                }
                $archivo = $request->file('comprobante');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $archivo->storeAs('public/comprobantes', $nombreArchivo);
            }

            $subtotal = $validatedData['monto_subtotal'];
            $iva = isset($validatedData['monto_iva']) ? $validatedData['monto_iva'] : 0;
            
            $requiereAprobacion = $request->has('requiere_aprobacion');
            // Si se edita, se resetea el estado para posible re-aprobación.
            $estado = $requiereAprobacion ? 'En Aprobación' : 'Aprobado';

            $gasto->update([
                'fecha_gasto' => $validatedData['fecha_gasto'],
                'sucursal_id' => $validatedData['sucursal_id'],
                'proveedor_id' => $proveedor->id,
                'categoria_id' => $validatedData['categoria_id'],
                'descripcion' => $validatedData['descripcion'],
                'monto_subtotal' => $subtotal,
                'monto_iva' => $iva,
                'monto_total' => $subtotal + $iva,
                'nombre_archivo_comprobante' => $nombreArchivo,
                'requiere_aprobacion' => $requiereAprobacion,
                'estado' => $estado,
                'comentarios_rechazo' => null,
            ]);

            DB::commit();
            return redirect()->route('gastos.index')->with('success', 'Gasto actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hubo un error al actualizar el gasto: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Elimina un gasto.
     */
    public function destroy(Gasto $gasto)
    {
        try {
            if ($gasto->nombre_archivo_comprobante) {
                Storage::delete('public/comprobantes/' . $gasto->nombre_archivo_comprobante);
            }
            $gasto->delete();
            return redirect()->route('gastos.index')->with('success', 'Gasto eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Hubo un error al eliminar el gasto: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el archivo de un comprobante.
     */
    public function verComprobante(Gasto $gasto)
    {
        if (!$gasto->nombre_archivo_comprobante) {
            abort(404, 'Comprobante no encontrado.');
        }
        $rutaArchivo = 'public/comprobantes/' . $gasto->nombre_archivo_comprobante;
        if (!Storage::exists($rutaArchivo)) {
            abort(404, 'El archivo del comprobante no existe en el servidor.');
        }
        return Storage::response($rutaArchivo);
    }

    /**
     * Muestra la lista de gastos pendientes de aprobación.
     */
    public function approvalIndex()
    {
        $gastosPendientes = Gasto::with(['sucursal', 'categoria', 'proveedor', 'usuario'])
                                    ->where('estado', 'En Aprobación')
                                    ->latest('fecha_gasto')
                                    ->paginate(15);

        return view('gastos.approvals', compact('gastosPendientes'));
    }

    /**
     * Aprueba un gasto.
     */
    public function approve(Gasto $gasto)
    {
        $gasto->update(['estado' => 'Aprobado']);
        return back()->with('success', 'El gasto ha sido aprobado.');
    }

    /**
     * Rechaza un gasto.
     */
    public function reject(Request $request, Gasto $gasto)
    {
        $request->validate(['comentarios_rechazo' => 'required|string|max:500']);

        $gasto->update([
            'estado' => 'Rechazado',
            'comentarios_rechazo' => $request->comentarios_rechazo,
        ]);

        return back()->with('success', 'El gasto ha sido rechazado.');
    }
}
