<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Journal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date', // <-- ¡ESTA ES LA LÍNEA QUE FALTABA!
        'concept',
        'sourceable_id',
        'sourceable_type',
    ];

    /**
     * Una póliza tiene muchos asientos/movimientos.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Obtiene el modelo origen (Gasto, Venta, etc.) que generó esta póliza.
     */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
