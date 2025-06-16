<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoVacacional extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'periodos_vacacionales';

    /**
     * The primary key associated with the table.
     * (Laravel asume 'id' por defecto si usaste $table->id() en la migración)
     *
     * @var string
     */
    // protected $primaryKey = 'id_periodo_vacacional'; // Solo si nombraste tu PK diferente a 'id'

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_empleado',
        'fecha_inicio',
        'fecha_fin',
        'dias_tomados',
        'ano_servicio_correspondiente',
        'comentarios',
    ];

    /**
     * The attributes that should be cast.
     * Esto le dice a Laravel que trate estos campos como objetos Carbon (fechas).
     *
     * @var array
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Define la relación: Un PeriodoVacacional pertenece a un Empleado.
     */
    public function empleado()
    {
        // Asegúrate que los nombres de las llaves coincidan con tu tabla empleados
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}