<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';

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
        
        // --- REGLAS DE DISCIPLINA ACTUALIZADAS ---
        'aplicar_reglas_avanzadas',
        'tolerancia_minutos',
        'minutos_limite_retardo',
        'retardos_por_falta',
        'aplica_medio_dia',
        'minutos_limite_medio_dia',
        'aplica_castigo_multiplicador',
        'multiplicador_lunes_viernes',
        'multiplicador_dias_regulares'
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_horario', 'id_horario');
    }
}