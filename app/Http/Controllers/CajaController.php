<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Caja;
use App\Models\CorteCaja;
use Carbon\Carbon;

class CajaController extends Controller
{
    /**
     * Pantalla principal: Muestra las cajas y si el usuario tiene un turno abierto.
     */
    public function index()
    {
        // 1. Revisamos si el admin ya tiene un turno abierto
        $turnoActivo = CorteCaja::where('usuario_id', auth()->id())
                                ->where('estatus', 'abierto')
                                ->with('caja')
                                ->first();

        if ($turnoActivo) {
            return redirect()->route('cajas.operacion');
        }

        // 2. 🔥 AUTOMATIZACIÓN: 1 CAJA POR SUCURSAL 🔥
        // Traemos todas las sucursales (tu Global Scope ya filtra y solo trae las Activas)
        $sucursalesActivas = \App\Models\Sucursal::all();

        foreach ($sucursalesActivas as $sucursal) {
            // firstOrCreate busca si la sucursal ya tiene caja. Si no la tiene, la crea al instante.
            \App\Models\Caja::firstOrCreate(
                ['sucursal_id' => $sucursal->id_sucursal],
                [
                    'nombre' => 'Caja Principal',
                    'estatus' => 'cerrada',
                    'saldo_actual' => 0.00
                ]
            );
        }

        // 3. Ahora sí, mandamos a la vista las cajas asegurándonos que su sucursal siga activa
        $cajas = \App\Models\Caja::whereHas('sucursal')->with('sucursal')->get(); 

        return view('cajas.index', compact('cajas'));
    }

    /**
     * Abre un nuevo turno para el cajero.
     */
    public function abrirTurno(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'saldo_inicial' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $caja = Caja::findOrFail($request->caja_id);

            if ($caja->estatus === 'abierta') {
                return back()->with('error', 'La caja ya está abierta. Si hubo un error de sesión, cierra el turno anterior primero.');
            }

            // 1. Crear el corte de caja (Turno)
            $corte = CorteCaja::create([
                'caja_id' => $caja->id,
                'usuario_id' => auth()->id(),
                'fecha_apertura' => now(),
                'saldo_inicial' => $request->saldo_inicial,
                'saldo_teorico' => $request->saldo_inicial,
                'estatus' => 'abierto'
            ]);

            // 2. Actualizar el estatus
            $caja->update([
                'estatus' => 'abierta',
                'saldo_actual' => $request->saldo_inicial
            ]);

            DB::commit();

            return redirect()->route('cajas.operacion')->with('success', 'Turno abierto exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al abrir la caja: ' . $e->getMessage());
        }
    }

    /**
     * Pantalla de operación (Donde estará el buscador, alertas de mora y cobro).
     * La construiremos en el siguiente paso.
     */
    public function operacion()
    {
        $turnoActivo = CorteCaja::where('usuario_id', auth()->id())
                                ->where('estatus', 'abierto')
                                ->with('caja')
                                ->firstOrFail();

        // Traemos todos los créditos activos y sus cuotas no pagadas para el buscador
        $creditos = \App\Models\Credito::with([
            'cliente', 
            'grupo', 
            'amortizaciones' => function($q) {
                $q->where('estatus', '!=', 'pagado')->orderBy('numero_cuota', 'asc');
            }
        ])->where('estatus', 'desembolsado')->get();

        return view('cajas.operacion', compact('turnoActivo', 'creditos'));
    }
}