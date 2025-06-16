<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre_horario',
        'descripcion',
        'lunes', 'lunes_entrada', 'lunes_salida',
        'martes', 'martes_entrada', 'martes_salida',
        'miercoles', 'miercoles_entrada', 'miercoles_salida',
        'jueves', 'jueves_entrada', 'jueves_salida',
        'viernes', 'viernes_entrada', 'viernes_salida',
        'sabado', 'sabado_entrada', 'sabado_salida',
        'domingo', 'domingo_entrada', 'domingo_salida',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'lunes' => 'boolean',
        'martes' => 'boolean',
        'miercoles' => 'boolean',
        'jueves' => 'boolean',
        'viernes' => 'boolean',
        'sabado' => 'boolean',
        'domingo' => 'boolean',
    ];

    /**
     * Relación: Un Horario puede ser asignado a muchos Empleados.
     */
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_horario', 'id_horario');
    }
}