<?php
// app/Http/Controllers/CategoriaController.php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->paginate(10);
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:categorias,nombre|max:255',
            'default_requiere_aprobacion' => 'nullable|boolean',
        ]);

        Categoria::create([
            'nombre' => $validated['nombre'],
            'default_requiere_aprobacion' => $request->has('default_requiere_aprobacion'),
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => ['required','string','max:255', Rule::unique('categorias')->ignore($categoria->id)],
            'default_requiere_aprobacion' => 'nullable|boolean',
        ]);

        $categoria->update([
            'nombre' => $validated['nombre'],
            'default_requiere_aprobacion' => $request->has('default_requiere_aprobacion'),
        ]);

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