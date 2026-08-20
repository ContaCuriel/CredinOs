<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditoAmortizacion extends Model
{
    use HasFactory;

    protected $table = 'credito_amortizaciones';

    protected $fillable = [
        'credito_id',
        'numero_cuota',
        'fecha_pago',
        'saldo_inicial',
        'capital',
        'interes',
        'iva',
        'total_cuota',
        'saldo_final',
        'estatus'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'saldo_inicial' => 'float',
        'capital' => 'float',
        'interes' => 'float',
        'iva' => 'float',
        'total_cuota' => 'float',
        'saldo_final' => 'float',
    ];

    // Relación de reversa: A qué crédito pertenece esta cuota
    public function credito()
    {
        return $this->belongsTo(Credito::class, 'credito_id', 'id');
    }
}