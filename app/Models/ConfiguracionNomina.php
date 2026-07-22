<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionNomina extends Model
{
    use HasFactory;

    protected $table = 'configuracion_nomina';

    protected $fillable = [
        'retardos_para_falta',
        'descontar_septimo_dia',
        'metodo_calculo_dias',
        'pagar_dia_31',
        'redondear_neto',
    ];
}