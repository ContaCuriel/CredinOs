<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class IncomeStatementExport implements FromView, ShouldAutoSize
{
    protected $data;

    // Recibimos todos los datos calculados desde el controlador.
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Devuelve la vista de Blade que se convertirá en la hoja de Excel.
     */
    public function view(): View
    {
        // Pasamos el array de datos a una vista de exportación simple.
        return view('reportes.exports.income_statement_excel', $this->data);
    }
}
