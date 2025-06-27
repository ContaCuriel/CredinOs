<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * Display a listing of the resource.
     * Muestra la lista de todas las pólizas (Libro de Diario).
     */
    public function index()
    {
        // Obtenemos las pólizas ordenadas por la más reciente, con paginación.
        // Cargamos la relación 'sourceable' para saber qué originó la póliza (un Gasto).
        $journals = Journal::with('sourceable')
                            ->latest()
                            ->paginate(25);
                            
        return view('journals.index', compact('journals'));
    }

    /**
     * Display the specified resource.
     * Muestra los detalles de una póliza específica, incluyendo sus asientos.
     */
    public function show(Journal $journal)
    {
        // Cargamos los asientos de la póliza (entries) y, para cada asiento,
        // cargamos la información de la cuenta contable asociada.
        $journal->load('entries.account');
        
        return view('journals.show', compact('journal'));
    }
}
