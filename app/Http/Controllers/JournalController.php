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
        // CORRECCIÓN: Cargamos 'entries' para que el sum('debit') funcione 
        // y 'sourceable' para identificar el origen.
        $journals = Journal::with(['sourceable', 'entries'])
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
        // Cargamos los asientos y sus cuentas
        $journal->load('entries.account');
        
        return view('journals.show', compact('journal'));
    }
}
