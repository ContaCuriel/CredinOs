<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Cliente;
use App\Models\Group;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CreditType;
use App\Models\InterestRate;
use Illuminate\Support\Facades\DB;
use App\Models\Empleado;

class CreditoController extends Controller
{
    /**
     * Muestra el formulario para crear un nuevo crédito.
     * Este método prepara todos los datos necesarios para el formulario inteligente.
     */
   public function create()
{
    // ... (la carga de Clientes, Sucursales, etc., no cambia)
    $clientes = Cliente::orderBy('nombre')->get();
    $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
    $creditTypes = CreditType::orderBy('name')->get();
    $interestRates = InterestRate::orderBy('rate')->get();

    // --- CONSULTA CORREGIDA USANDO LA RELACIÓN 'puesto' ---
    $asesores = Empleado::whereHas('puesto', function ($query) {
        // Buscamos en la tabla relacionada 'puestos'
        $query->where('nombre_puesto', 'like', 'ASESOR%')
              ->orWhere('nombre_puesto', 'like', 'GERENTE%');
    })
    ->orderBy('nombre_completo')
    ->get();

    return view('creditos.create', compact(
        'clientes',
        'sucursales',
        'asesores',
        'creditTypes',
        'interestRates'
    ));
}
    /**
     * Guarda un nuevo crédito (individual o grupal) en la base de datos.
     */
    public function store(Request $request)
    {
        // Validación (puedes moverla a un FormRequest después)
        $validated = $request->validate([
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'id_asesor' => 'required|exists:users,id',
            'fecha_solicitud' => 'required|date',
            'credit_type_id' => 'required|exists:credit_types,id',
            'monto_solicitado' => 'required|numeric|min:1',
            'interest_rate_id' => 'required|exists:interest_rates,id',
            'cliente_ids' => 'required|array|min:1',
            'cliente_ids.*' => 'exists:clientes,id_cliente',
            'montos_individuales' => 'required|array',
            'montos_individuales.*' => 'numeric|min:0',
            'nombre_grupo' => 'nullable|string|max:255',
            'disbursement_bank' => 'required|string|max:100',
            'disbursement_account_number' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $creditType = CreditType::findOrFail($validated['credit_type_id']);
            $interestRate = InterestRate::findOrFail($validated['interest_rate_id']);
            $loanable = null;

            // 1. Si es grupal, creamos el grupo primero
            if ($creditType->is_group_loan) {
                if(empty($validated['nombre_grupo'])) {
                    throw new \Exception("El nombre del grupo es requerido para créditos grupales.");
                }
                $group = Group::create([
                    'nombre_grupo' => $validated['nombre_grupo'],
                    'id_sucursal' => $validated['id_sucursal'],
                    'id_asesor' => $validated['id_asesor'],
                ]);
                // Adjuntamos los clientes seleccionados al nuevo grupo
                $group->clientes()->sync($validated['cliente_ids']);
                $loanable = $group;
            } else {
                // Si es individual, el acreditable es el primer y único cliente
                $loanable = Cliente::find($validated['cliente_ids'][0]);
            }

            // 2. Creamos el crédito
            $credito = new Credito([
                'id_sucursal' => $validated['id_sucursal'],
                'id_asesor' => $validated['id_asesor'],
                'credit_type_id' => $validated['credit_type_id'],
                'monto_solicitado' => $validated['monto_solicitado'],
                'plazo' => $creditType->default_term,
                'frecuencia' => $creditType->term_frequency,
                'tasa_interes' => $interestRate->rate,
                'tasa_interes_moratoria' => $interestRate->late_fee ?? $creditType->late_interest_rate, // Opcional
                'fecha_solicitud' => $validated['fecha_solicitud'],
                'status' => 'Pendiente Aprobacion',
                'reference_number' => 'CR-' . strtoupper(uniqid()),
                'disbursement_bank' => $validated['disbursement_bank'],
                'disbursement_account_number' => $validated['disbursement_account_number'],
            ]);

            // Asociamos el crédito con el cliente o grupo
            $credito->loanable()->associate($loanable);
            $credito->save();

            // 3. Guardamos los montos individuales en la tabla pivote credito_cliente
            $montosIndividuales = [];
            foreach ($validated['montos_individuales'] as $cliente_id => $monto) {
                if ($monto > 0) {
                    $montosIndividuales[$cliente_id] = ['individual_amount' => $monto];
                }
            }
            $credito->members()->sync($montosIndividuales);


            DB::commit();

            return redirect()->route('creditos.index')->with('success', 'Solicitud de crédito registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el crédito: ' . $e->getMessage())->withInput();
        }
    }
    
    // Dejaremos los otros métodos listos para implementarlos después
    public function index()
{
    // Usamos with('loanable') para cargar eficientemente el dueño del crédito 
    // (sea Cliente o Grupo) y evitar múltiples consultas a la base de datos.
    $creditos = Credito::with('loanable', 'sucursal', 'asesor')
                        ->latest('fecha_solicitud') // Ordena por fecha de solicitud, los más nuevos primero
                        ->paginate(15);

    return view('creditos.index', compact('creditos'));
}

    // No olvides añadir 'use App\Services\AmortizationService;' al principio del controlador

public function disburse(Request $request, Credito $credito, AmortizationService $amortizationService)
{
    // Actualizamos el crédito a Activo y establecemos la fecha de desembolso
    $credito->update([
        'status' => 'Activo',
        'monto_autorizado' => $credito->monto_solicitado, // Por ahora, autorizamos el monto solicitado
        'fecha_desembolso' => now(),
    ]);

    // Llamamos a nuestro servicio para que genere el plan de pagos
    $amortizationService->generateSchedule($credito);

    return redirect()->route('creditos.show', $credito->id_credito)
                     ->with('success', 'Crédito desembolsado y plan de pagos generado exitosamente.');
}

    public function show(Credito $credito) { /* Lógica para ver un crédito */ }
    public function edit(Credito $credito) { /* Lógica para editar un crédito */ }
    public function update(Request $request, Credito $credito) { /* Lógica para actualizar */ }
    public function destroy(Credito $credito) { /* Lógica para eliminar */ }
}