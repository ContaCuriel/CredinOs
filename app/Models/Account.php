<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'parent_id',
    ];

    /**
     * Define la relación para la cuenta padre.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Define la relación para las cuentas hijas.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Define la relación con los asientos contables.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Calcula los movimientos de una cuenta (y sus hijas) en un rango de fechas.
     */
    public function getMovements($startDate, $endDate)
    {
        // CORRECCIÓN: Usamos un JOIN para filtrar por la fecha de la póliza.
        $debits = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->whereBetween('journals.date', [$startDate, $endDate])
            ->sum('journal_entries.debit');

        $credits = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->whereBetween('journals.date', [$startDate, $endDate])
            ->sum('journal_entries.credit');

        // Suma recursiva de los movimientos de las cuentas hijas.
        foreach ($this->children as $child) {
            $childMovements = $child->getMovements($startDate, $endDate);
            $debits += $childMovements['debits'];
            $credits += $childMovements['credits'];
        }

        return ['debits' => $debits, 'credits' => $credits];
    }

    /**
     * Calcula el saldo inicial de la cuenta (y sus hijas) antes de una fecha.
     */
    public function getInitialBalance($startDate)
    {
        // Llama a una función auxiliar para obtener los totales brutos.
        $rawBalance = $this->getRawBalanceBefore($startDate);
        
        // Calcula el saldo dependiendo de la naturaleza de la cuenta.
        if (in_array($this->type, ['activo', 'gastos'])) {
            return $rawBalance['debits'] - $rawBalance['credits'];
        } else {
            return $rawBalance['credits'] - $rawBalance['debits'];
        }
    }

    /**
     * Función auxiliar recursiva para obtener el debe y haber acumulado.
     * Esto corrige el error original.
     */
    protected function getRawBalanceBefore($startDate)
    {
        // CORRECCIÓN: Usamos un JOIN para filtrar por la fecha de la póliza.
        $debitsBefore = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->where('journals.date', '<', $startDate)
            ->sum('journal_entries.debit');

        $creditsBefore = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->where('journals.date', '<', $startDate)
            ->sum('journal_entries.credit');

        // Suma recursiva de los saldos de las cuentas hijas.
        foreach ($this->children as $child) {
            $childRawBalance = $child->getRawBalanceBefore($startDate);
            $debitsBefore += $childRawBalance['debits'];
            $creditsBefore += $childRawBalance['credits'];
        }
        
        return ['debits' => $debitsBefore, 'credits' => $creditsBefore];
    }
}
