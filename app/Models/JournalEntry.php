<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'journal_id',
        'account_id', // <-- ¡ESTA ES LA LÍNEA QUE FALTABA!
        'debit',
        'credit',
    ];

    /**
     * Un asiento pertenece a una póliza.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Un asiento afecta a una cuenta contable.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
