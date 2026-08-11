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
// Importamos tu servicio contable
use App\Services\AccountingService; 

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
     * Inyectamos AccountingService para generar la póliza si entra directo como Aprobado.
     */
    public function store(Request $request, AccountingService $accountingService)
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('GastoController@store: Error de validación.', ['errors' => $e->errors()]);
            throw $e;
        }

        DB::beginTransaction();
        try {
            $proveedor = Proveedor::firstOrCreate(['nombre' => $validatedData['proveedor_nombre']]);

            $nombreArchivo = null;
            if ($request->hasFile('comprobante')) {
                $archivo = $request->file('comprobante');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $archivo->storeAs('public/comprobantes', $nombreArchivo);
            }

            $subtotal = $validatedData['monto_subtotal'];
            $iva = isset($validatedData['monto_iva']) ? $validatedData['monto_iva'] : 0;
            
            $requiereAprobacion = $request->has('requiere_aprobacion');
            $estado = $requiereAprobacion ? 'En Aprobación' : 'Aprobado';

            $gasto = Gasto::create([
                'fecha_gasto' => $validatedData['fecha_gasto'],
                'sucursal_id' => $validatedData['sucursal_id'],
                'proveedor_id' => $proveedor->id,
                'categoria_id' => $validatedData['categoria_id'],
                'usuario_registra_id' => Auth::id(),
                'descripcion' => $validatedData['descripcion'],
                'monto_subtotal' => $subtotal,
                'monto_iva' => $iva,
                'monto_total' => $subtotal + $iva,
                'nombre_archivo_comprobante' => $nombreArchivo,
                'requiere_aprobacion' => $requiereAprobacion,
                'estado' => $estado,
            ]);

            // ===== LÓGICA CONTABLE (PÓLIZA) =====
            // Si NO requiere aprobación, creamos la póliza contable de inmediato.
            if ($estado === 'Aprobado') {
                try {
                    $accountingService->createJournalFromExpense($gasto);
                } catch (\Exception $e) {
                    // Pantalla negra de seguridad para debugear la póliza
                    dd([
                        '¡ALERTA DE ERROR FATAL CONTABLE!' => 'El gasto se guardó, pero falló al crear la Póliza.',
                        'MENSAJE_EXACTO' => $e->getMessage(),
                        'ARCHIVO_DONDE_FALLO' => $e->getFile(),
                        'LINEA' => $e->getLine()
                    ]);
                }
            }

            DB::commit();
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
    public function update(Request $request, Gasto $gasto, AccountingService $accountingService)
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
            $estado = $requiereAprobacion ? 'En Aprobación' : 'Aprobado';

            // 1. Actualizamos el registro operativo del gasto
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

            // 2. LÓGICA CONTABLE: Actualizamos la póliza
            if ($estado === 'Aprobado') {
                // Destruye la póliza vieja y crea una nueva con los datos frescos
                $accountingService->updateJournalFromExpense($gasto);
            } else {
                // Si lo regresaron a "En Aprobación", borramos la póliza que tenía
                $accountingService->deleteJournalForModel($gasto);
            }

            DB::commit();
            return redirect()->route('gastos.index')->with('success', 'Gasto actualizado y póliza regenerada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hubo un error al actualizar el gasto: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Elimina un gasto.
     */
    public function destroy(Gasto $gasto, AccountingService $accountingService)
    {
        try {
            if ($gasto->nombre_archivo_comprobante) {
                Storage::delete('public/comprobantes/' . $gasto->nombre_archivo_comprobante);
            }
            
            // 1. Destruimos la póliza contable primero
            $accountingService->deleteJournalForModel($gasto);
            
            // 2. Destruimos el registro operativo
            $gasto->delete();
            
            return redirect()->route('gastos.index')->with('success', 'Gasto y póliza eliminados exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Hubo un error al eliminar: ' . $e->getMessage());
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
     * Inyectamos el AccountingService para generar la póliza al momento de aprobar.
     */
    public function approve(Gasto $gasto, AccountingService $accountingService)
    {
        DB::beginTransaction();
        try {
            $gasto->update(['estado' => 'Aprobado']);
            
            // ===== LÓGICA CONTABLE (PÓLIZA) =====
            // Generamos la póliza contable porque el gerente acaba de autorizar la salida de dinero
            $accountingService->createJournalFromExpense($gasto);

            DB::commit();
            return back()->with('success', 'El gasto ha sido aprobado y la póliza generada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Pantalla negra de seguridad
            dd([
                '¡ALERTA DE ERROR FATAL CONTABLE!' => 'El gasto NO pudo ser aprobado porque falló la creación de la póliza.',
                'MENSAJE_EXACTO' => $e->getMessage(),
                'ARCHIVO_DONDE_FALLO' => $e->getFile(),
                'LINEA' => $e->getLine()
            ]);
        }
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