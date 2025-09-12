<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finiquito extends Model
{
    use HasFactory;

    protected $table = 'finiquitos';
    // Laravel asume 'id' como llave primaria por defecto.

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_empleado',
        'tipo_calculo',
        'fecha_ingreso_usada',
        'fecha_baja_usada',
        'salario_diario_usado',
        'monto_dias_laborados',
        'monto_vacaciones',
        'monto_prima_vacacional',
        'monto_aguinaldo',
        'monto_bono_permanencia',
        'monto_bono_cumpleanos',
        'monto_3_meses',
        'monto_prima_antiguedad',
        'monto_caja_ahorro',
        'deduccion_prestamo',
        'total_percepciones',
        'total_deducciones',
        'neto_a_pagar',
        'notas',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'fecha_ingreso_usada' => 'date',
        'fecha_baja_usada' => 'date',
        'salario_diario_usado' => 'decimal:2',
        'monto_dias_laborados' => 'decimal:2',
        'monto_vacaciones' => 'decimal:2',
        'monto_prima_vacacional' => 'decimal:2',
        'monto_aguinaldo' => 'decimal:2',
        'monto_bono_permanencia' => 'decimal:2',
        'monto_bono_cumpleanos' => 'decimal:2',
        'monto_3_meses' => 'decimal:2',
        'monto_prima_antiguedad' => 'decimal:2',
        'monto_caja_ahorro' => 'decimal:2',
        'deduccion_prestamo' => 'decimal:2',
        'total_percepciones' => 'decimal:2',
        'total_deducciones' => 'decimal:2',
        'neto_a_pagar' => 'decimal:2',
    ];

    /**
     * Relación: Un registro de finiquito pertenece a un Empleado.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}