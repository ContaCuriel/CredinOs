<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'year',
        'month',
        'capital_recovered',
        'interest_collected',
        'unrecoverable_amount',
        'user_id',
        'notes',
    ];

    /**
     * Una recuperación pertenece a una sucursal.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id_sucursal');
    }

    /**
     * Una recuperación fue registrada por un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la póliza contable asociada a esta recuperación.
     */
    public function journal()
    {
        return $this->morphOne(Journal::class, 'sourceable');
    }
}
