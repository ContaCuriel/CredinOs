<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use App\Models\Sucursal;
use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PlacementController extends Controller
{
    public function index()
    {
        $placements = Placement::with(['sucursal', 'user'])->latest()->paginate(20);
        return view('placements.index', compact('placements'));
    }

    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('placements.create', compact('sucursales'));
    }

    public function store(Request $request)
    {
        try {
            $currentYear = Carbon::now()->year;

            $validatedData = $request->validate([
                'sucursal_id' => 'required|exists:sucursales,id_sucursal',
                'year' => 'required|integer|min:2020|max:' . $currentYear,
                'month' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:12',
                    'Rule' => Rule::unique('placements')->where(function ($query) use ($request) {
                        return $query->where('sucursal_id', $request->sucursal_id)
                                     ->where('year', $request->year);
                    }),
                ],
                'amount' => 'required|numeric|min:0.01',
                'notes' => 'nullable|string',
            ]);

            // Verificamos que las cuentas existan antes de abrir la transacción
            $cuentaClientes = Account::where('code', '105.01')->first();
            $cuentaBancos = Account::where('code', '102.01')->first();

            if (!$cuentaClientes || !$cuentaBancos) {
                return redirect()->back()->with('error', 'Error Contable: Las cuentas 105.01 (Clientes) o 102.01 (Bancos) no existen en el catálogo.');
            }

            // Iniciamos la transacción segura
            DB::beginTransaction();

            // 1. Guardar la colocación operativa
            $placement = Placement::create([
                'sucursal_id' => $validatedData['sucursal_id'],
                'year'        => $validatedData['year'],
                'month'       => $validatedData['month'],
                'amount'      => $validatedData['amount'],
                'user_id'     => Auth::id() ?? 1,
                'notes'       => $validatedData['notes'],
            ]);

            // 2. LA MAGIA CONTABLE: Crear la Póliza de Diario/Egreso
            $fechaPoliza = Carbon::createFromDate($validatedData['year'], $validatedData['month'], 1)->endOfMonth();

            $journal = Journal::create([
                'date'            => $fechaPoliza,
                'concept'         => "Colocación mensual de cartera - Sucursal ID {$validatedData['sucursal_id']}",
                'sucursal_id'     => $validatedData['sucursal_id'], // <-- Corregido para coincidir con el modelo
                'user_id'         => Auth::id() ?? 1,
                'sourceable_id'   => $placement->id,                // <-- Enlazamos con la colocación
                'sourceable_type' => Placement::class,              // <-- Le decimos de qué modelo viene
            ]);

            // Cargo a Clientes (Activo aumenta)
            JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $cuentaClientes->id,
                'debit'      => $validatedData['amount'],
                'credit'     => 0,
            ]);

            // Abono a Bancos (Activo disminuye)
            JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $cuentaBancos->id,
                'debit'      => 0,
                'credit'     => $validatedData['amount'],
            ]);

            // Si llegamos hasta aquí, todo salió bien. Guardamos los cambios.
            DB::commit();

            return redirect()->route('placements.index')->with('success', 'Colocación guardada y póliza contable generada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Si algo falla, deshacemos cualquier inserción a medias (Rollback)
            DB::rollBack();
            Log::error('Error fatal al guardar colocación y póliza: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Ocurrió un error en el sistema al intentar guardar. El área de soporte ha sido notificada.')->withInput();
        }
    }
}