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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Calcula los movimientos de una cuenta (y sus hijas) filtrando opcionalmente por sucursal.
     */
    public function getMovements($startDate, $endDate, $sucursalId = null)
    {
        $queryDebits = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->whereBetween('journals.date', [$startDate, $endDate]);

        $queryCredits = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->whereBetween('journals.date', [$startDate, $endDate]);

        // FILTRO DE SUCURSAL (Asegúrate de que la columna se llame 'id_sucursal' en tu tabla journals)
        if ($sucursalId) {
            $queryDebits->where('journals.id_sucursal', $sucursalId);
            $queryCredits->where('journals.id_sucursal', $sucursalId);
        }

        $debits = $queryDebits->sum('journal_entries.debit');
        $credits = $queryCredits->sum('journal_entries.credit');

        // Suma recursiva de los movimientos de las cuentas hijas pasando la sucursal
        foreach ($this->children as $child) {
            $childMovements = $child->getMovements($startDate, $endDate, $sucursalId);
            $debits += $childMovements['debits'];
            $credits += $childMovements['credits'];
        }

        return ['debits' => $debits, 'credits' => $credits];
    }

    /**
     * Calcula el saldo inicial filtrando opcionalmente por sucursal.
     */
    public function getInitialBalance($startDate, $sucursalId = null)
    {
        $rawBalance = $this->getRawBalanceBefore($startDate, $sucursalId);
        
        // Normalizamos el tipo a minúsculas para evitar errores de comparación
        $type = strtolower($this->type);

        // 🔥 CORRECCIÓN: Agregamos 'costos' y 'cost' a la naturaleza deudora
        if (in_array($type, ['activo', 'asset', 'gastos', 'expense', 'costos', 'cost'])) {
            return $rawBalance['debits'] - $rawBalance['credits'];
        } else {
            // Pasivo, Capital, Ingresos
            return $rawBalance['credits'] - $rawBalance['debits'];
        }
    }

    /**
     * Función auxiliar recursiva para obtener el debe y haber acumulado con filtro de sucursal.
     */
    protected function getRawBalanceBefore($startDate, $sucursalId = null)
    {
        $queryDebits = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->where('journals.date', '<', $startDate);

        $queryCredits = $this->journalEntries()
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->where('journals.date', '<', $startDate);

        // FILTRO DE SUCURSAL
        if ($sucursalId) {
            $queryDebits->where('journals.id_sucursal', $sucursalId);
            $queryCredits->where('journals.id_sucursal', $sucursalId);
        }

        $debitsBefore = $queryDebits->sum('journal_entries.debit');
        $creditsBefore = $queryCredits->sum('journal_entries.credit');

        foreach ($this->children as $child) {
            $childRawBalance = $child->getRawBalanceBefore($startDate, $sucursalId);
            $debitsBefore += $childRawBalance['debits'];
            $creditsBefore += $childRawBalance['credits'];
        }
        
        return ['debits' => $debitsBefore, 'credits' => $creditsBefore];
    }
}