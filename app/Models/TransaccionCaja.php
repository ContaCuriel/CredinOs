<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaccionCaja extends Model
{
    use HasFactory;

    protected $table = 'transacciones_caja';

    protected $fillable = [
        'corte_caja_id', 
        'tipo', 
        'concepto', 
        'monto', 
        'metodo_pago', 
        'referencia_id', 
        'descripcion'
    ];

    public function corte()
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }
}