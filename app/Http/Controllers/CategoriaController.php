<?php
// app/Http/Controllers/CategoriaController.php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Account;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->paginate(10);
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        // Cargamos solo las cuentas de GASTO que no son "padres" para el selector.
        $accounts = Account::where('type', 'gastos')->whereDoesntHave('children')->orderBy('code')->get();
        
        return view('categorias.create', compact('accounts')); // Asegúrate que el nombre de la vista sea el correcto
    }

     public function store(Request $request)
    {
        // Añadimos la validación para el nuevo campo
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'default_requiere_aprobacion' => 'required|boolean',
            'account_id' => 'nullable|exists:accounts,id'
        ]);

        Categoria::create($validatedData);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(Categoria $categoria)
    {
        // También cargamos las cuentas aquí para el formulario de edición
        $accounts = Account::where('type', 'gastos')->whereDoesntHave('children')->orderBy('code')->get();

        return view('categorias.edit', compact('categoria', 'accounts')); // Asegúrate que el nombre de la vista sea el correcto
    }


    public function update(Request $request, Categoria $categoria)
    {
        // Añadimos la validación para el nuevo campo
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'default_requiere_aprobacion' => 'required|boolean',
            'account_id' => 'nullable|exists:accounts,id'
        ]);

        $categoria->update($validatedData);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Categoria $categoria)
    {
        // IMPORTANTE: Verificamos si la categoría está siendo usada en algún gasto.
        if ($categoria->gastos()->exists()) {
            return back()->with('error', 'No se puede eliminar la categoría porque ya está siendo utilizada en uno o más gastos.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}