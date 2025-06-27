<?php

namespace App\Exports;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TrialBalanceExport implements FromView, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Devuelve una vista de Blade que será convertida en una hoja de Excel.
     */
    public function view(): View
    {
        // Obtenemos los datos de la misma forma que en el controlador.
        $accounts = Account::with('children')->whereNull('parent_id')->orderBy('code')->get();

        // Le pasamos los datos a una vista especial para la exportación.
        return view('reportes.exports.trial_balance_excel', [
            'accounts' => $accounts,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }
}
