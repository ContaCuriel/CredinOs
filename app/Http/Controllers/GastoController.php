<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;
use App\Models\Categoria; // Importa el modelo Categoria
use App\Models\Proveedor; // Importa el modelo Proveedor
use Illuminate\Support\Facades\Auth; // Importa el facade de Autenticación
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class GastoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Obtenemos los datos para los dropdowns de los filtros
    $categorias = Categoria::orderBy('nombre')->get();
    $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

    // Empezamos la consulta base, con las relaciones para evitar N+1 queries
    $query = Gasto::with(['sucursal', 'categoria', 'proveedor', 'usuario']);

    // --- APLICACIÓN DE FILTROS ---

    // Filtro por término de búsqueda (en descripción o nombre del proveedor)
    if ($request->filled('search_term')) {
        $searchTerm = $request->input('search_term');
        $query->where(function($q) use ($searchTerm) {
            $q->where('descripcion', 'like', '%' . $searchTerm . '%')
              ->orWhereHas('proveedor', function($q_proveedor) use ($searchTerm) {
                  $q_proveedor->where('nombre', 'like', '%' . $searchTerm . '%');
              });
        });
    }

    // Filtro por sucursal
    if ($request->filled('sucursal_id')) {
        $query->where('sucursal_id', $request->input('sucursal_id'));
    }

    // Filtro por categoría
    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->input('categoria_id'));
    }

    // Filtro por estado
    if ($request->filled('estado')) {
        $query->where('estado', $request->input('estado'));
    }

    // Filtro por rango de fechas
    if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
        $query->whereBetween('fecha_gasto', [$request->input('fecha_inicio'), $request->input('fecha_fin')]);
    }

    // Ordenamos y paginamos el resultado FINAL de la consulta
    $gastos = $query->latest('fecha_gasto')->paginate(15);
    
    // Devolvemos la vista con los datos para la tabla y para los filtros
    return view('gastos.index', compact('gastos', 'categorias', 'sucursales'));
}

    /**
     * Show the form for creating a new resource.
     */
     public function create()
{
    $categorias = Categoria::orderBy('nombre')->get();
    $proveedores = Proveedor::orderBy('nombre')->get();

    // ¡Esta línea ahora funciona perfectamente gracias a la nueva relación en el modelo User!
    $sucursalUsuario = Auth::user()->sucursal; 
    // También necesitarás una lista de todas las sucursales para el dropdown
    $sucursales = Sucursal::orderBy('nombre_sucursal')->get(); //Ajusta el nombre de la columna si es diferente

    return view('gastos.create', [
        'categorias' => $categorias,
        'proveedores' => $proveedores,
        'sucursalUsuario' => $sucursalUsuario,
        'sucursales' => $sucursales, // La pasamos a la vista
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        // 1. --- VALIDACIÓN DE DATOS ---
        // Aquí definimos las reglas para cada campo del formulario.
        $validatedData = $request->validate([
            'fecha_gasto' => 'required|date',
            'sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'proveedor_nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'descripcion' => 'nullable|string',
            'monto_subtotal' => 'required|numeric|min:0',
            'monto_iva' => 'nullable|numeric|min:0',
            // El checkbox, si no se marca, no se envía. Con 'boolean' lo manejamos.
            'requiere_aprobacion' => 'nullable|boolean',
            'comprobante' => 'nullable|file|mimes:jpg,png,pdf,xml|max:2048' // 2MB Max
        ]);

        // Usamos una transacción para asegurar que todo se guarde correctamente, o nada se guarde si hay un error.
        DB::beginTransaction();

        try {
            // 2. --- PROCESAMIENTO DE DATOS ---

            // A. Manejar el Proveedor:
            // Busca un proveedor con ese nombre. Si no lo encuentra, lo crea.
            // Esto evita duplicados y construye tu catálogo dinámicamente.
            $proveedor = Proveedor::firstOrCreate(
                ['nombre' => $validatedData['proveedor_nombre']]
            );
            
            // B. Manejar el archivo del Comprobante (si se subió uno)
            $nombreArchivo = null;
            if ($request->hasFile('comprobante')) {
                // Generamos un nombre único para el archivo y lo guardamos
                $archivo = $request->file('comprobante');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                // Lo guardamos en 'storage/app/public/comprobantes'
                $archivo->storeAs('public/comprobantes', $nombreArchivo);
            }

            // C. Preparar los datos para guardar
            $subtotal = $validatedData['monto_subtotal'];
            $iva = $validatedData['monto_iva'] ?? 0; // Si el IVA es nulo, lo tratamos como 0

            // 3. --- GUARDADO EN LA BASE DE DATOS ---
            Gasto::create([
                'fecha_gasto' => $validatedData['fecha_gasto'],
                'sucursal_id' => $validatedData['sucursal_id'],
                'proveedor_id' => $proveedor->id, // Usamos el ID del proveedor encontrado o creado
                'categoria_id' => $validatedData['categoria_id'],
                'usuario_registra_id' => Auth::id(), // ID del usuario autenticado
                'descripcion' => $validatedData['descripcion'],
                'monto_subtotal' => $subtotal,
                'monto_iva' => $iva,
                'monto_total' => $subtotal + $iva, // Calculamos el total en el backend por seguridad
                'nombre_archivo_comprobante' => $nombreArchivo,
                // Si el checkbox "requiere_aprobacion" se marcó, su valor es true. Si no, false.
                'requiere_aprobacion' => $request->has('requiere_aprobacion'),
                // Determinamos el estado inicial basado en si requiere o no aprobación
                'estado' => $request->has('requiere_aprobacion') ? 'En Aprobación' : 'Aprobado (Automático)',
            ]);

            // Si todo salió bien, confirmamos la transacción
            DB::commit();

            // Redirigimos al usuario a una página de lista (que crearemos después) con un mensaje de éxito.
           return redirect()->route('gastos.index')->with('success', '¡Gasto registrado exitosamente!');

        } catch (\Exception $e) {
            // Si algo falla, revertimos todos los cambios en la base de datos
            DB::rollBack();

            // Y redirigimos de vuelta al formulario con un mensaje de error
            return back()->with('error', 'Hubo un error al registrar el gasto: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gasto $gasto)
{
    // Obtenemos los datos para los dropdowns
    $categorias = Categoria::orderBy('nombre')->get();
    $proveedores = Proveedor::orderBy('nombre')->get();
    $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

    return view('gastos.edit', compact('gasto', 'categorias', 'proveedores', 'sucursales'));
}

    /**
     * Update the specified resource in storage.
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
            // Si hay un archivo nuevo, borramos el antiguo (si existe)
            if ($nombreArchivo) {
                Storage::delete('public/comprobantes/' . $nombreArchivo);
            }
            $archivo = $request->file('comprobante');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $archivo->storeAs('public/comprobantes', $nombreArchivo);
        }

        $subtotal = $validatedData['monto_subtotal'];
        $iva = $validatedData['monto_iva'] ?? 0;

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
            'requiere_aprobacion' => $request->has('requiere_aprobacion'),
            // Opcional: Si se edita, se puede resetear el estado a "En Aprobación"
            'estado' => $request->has('requiere_aprobacion') ? 'En Aprobación' : 'Aprobado (Automático)',
            'comentarios_rechazo' => null, // Limpiamos comentarios de rechazo previos
        ]);

        DB::commit();
        return redirect()->route('gastos.index')->with('success', 'Gasto actualizado exitosamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Hubo un error al actualizar el gasto: ' . $e->getMessage())->withInput();
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gasto $gasto)
{
    try {
        // Borramos el archivo del comprobante del servidor si existe
        if ($gasto->nombre_archivo_comprobante) {
            Storage::delete('public/comprobantes/' . $gasto->nombre_archivo_comprobante);
        }
        
        $gasto->delete();

        return redirect()->route('gastos.index')->with('success', 'Gasto eliminado exitosamente.');
    } catch (\Exception $e) {
        return back()->with('error', 'Hubo un error al eliminar el gasto: ' . $e->getMessage());
    }
}

     public function verComprobante(Gasto $gasto)
    {
        // Verificamos que el gasto realmente tenga un archivo asociado
        if (!$gasto->nombre_archivo_comprobante) {
            abort(404, 'Comprobante no encontrado.');
        }

        $rutaArchivo = 'public/comprobantes/' . $gasto->nombre_archivo_comprobante;

        // Verificamos que el archivo físico exista en el disco
        if (!Storage::exists($rutaArchivo)) {
            abort(404, 'El archivo del comprobante no existe en el servidor.');
        }

        // Devolvemos el archivo directamente al navegador.
        // El navegador lo mostrará si puede (PDF, imagen) o lo descargará.
        return Storage::response($rutaArchivo);
    }

     public function approvalIndex()
    {
        $gastosPendientes = Gasto::with(['sucursal', 'categoria', 'proveedor', 'usuario'])
                                 ->where('estado', 'En Aprobación')
                                 ->latest('fecha_gasto')
                                 ->paginate(15);

        return view('gastos.approvals', compact('gastosPendientes'));
    }

public function approve(Gasto $gasto)
{
    $gasto->update(['estado' => 'Aprobado']);
    return back()->with('success', 'El gasto ha sido aprobado.');
}

/**
 * Rechaza un gasto pendiente.
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
