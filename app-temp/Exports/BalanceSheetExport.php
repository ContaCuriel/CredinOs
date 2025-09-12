<?php

namespace App\Exports;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BalanceSheetExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $endDate;
    protected $data;

    public function __construct(string $endDate, array $data)
    {
        $this->endDate = $endDate;
        $this->data = $data;
    }

    /**
     * Devuelve la vista de Blade que se convertirá en la hoja de Excel.
     */
    public function view(): View
    {
        return view('reportes.exports.balance_sheet_excel', array_merge($this->data, [
            'endDate' => $this->endDate,
        ]));
    }

    /**
     * Define el título de la hoja en el archivo Excel.
     */
    public function title(): string
    {
        return 'Balance General';
    }
}
