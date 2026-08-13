<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoCredito extends Model
{
    use HasFactory;

    protected $table = 'productos_credito';

    protected $fillable = [
        'nombre',
        'tipo_credito',
        'frecuencia_pago',
        'tasa_interes',
        'tipo_tasa',
        'plazo_minimo',
        'plazo_maximo',
        'monto_minimo',
        'monto_maximo',
        'hora_maxima_pago',
        'multa_trigger',
        'multa_valor',
        'multa_calculo',
        'mora_trigger',
        'mora_valor',
        'mora_calculo',
        'politica_acumulacion',
        'activo'
    ];

    protected $casts = [
        'tasa_interes' => 'float',
        'monto_minimo' => 'float',
        'monto_maximo' => 'float',
        'multa_valor'  => 'float',
        'mora_valor'   => 'float',
        'activo'       => 'boolean',
        'plazo_minimo' => 'integer',
        'plazo_maximo' => 'integer',
    ];
}