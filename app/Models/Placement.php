<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Placement extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'year',
        'month',
        'amount',
        'user_id',
        'notes',
    ];

    public function journal(): MorphOne
    {
        return $this->morphOne(Journal::class, 'sourceable');
    }

    /**
     * Una colocación pertenece a una sucursal.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id_sucursal');
    }

    /**
     * Una colocación fue registrada por un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la póliza contable asociada a esta colocación.
     */
    public function journal()
    {
        return $this->morphOne(Journal::class, 'sourceable');
    }
}
