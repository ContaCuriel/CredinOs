<?php

namespace App\Http\Controllers;

use App\Models\CuentaBancaria;
use Illuminate\Http\Request;

class CuentaBancariaController extends Controller
{
    public function index()
    {
        $cuentas = CuentaBancaria::orderBy('banco')->get();
        return view('cuentas_bancarias.index', compact('cuentas'));
    }

    public function create()
    {
        return view('cuentas_bancarias.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'banco' => 'required|string|max:255',
            'titular' => 'required|string|max:255',
            'numero_cuenta' => 'nullable|string|max:255',
            'clabe' => 'nullable|string|max:255',
        ]);

        $validatedData['activa'] = true; // Por defecto al crear es activa

        CuentaBancaria::create($validatedData);

        return redirect()->route('cuentas_bancarias.index')->with('success', 'Cuenta bancaria registrada exitosamente.');
    }

    public function edit($id)
    {
        $cuenta = CuentaBancaria::findOrFail($id);
        return view('cuentas_bancarias.edit', compact('cuenta'));
    }

    public function update(Request $request, $id)
    {
        $cuenta = CuentaBancaria::findOrFail($id);

        $validatedData = $request->validate([
            'banco' => 'required|string|max:255',
            'titular' => 'required|string|max:255',
            'numero_cuenta' => 'nullable|string|max:255',
            'clabe' => 'nullable|string|max:255',
            'activa' => 'boolean'
        ]);

        // Si el checkbox no viene en el request, es porque lo desmarcaron
        if (!$request->has('activa')) {
            $validatedData['activa'] = false;
        }

        $cuenta->update($validatedData);

        return redirect()->route('cuentas_bancarias.index')->with('success', 'Cuenta bancaria actualizada exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $cuenta = CuentaBancaria::findOrFail($id);
            $cuenta->delete();
            return redirect()->route('cuentas_bancarias.index')->with('success', 'Cuenta eliminada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se puede eliminar esta cuenta porque ya tiene pagos asociados.');
        }
    }
}