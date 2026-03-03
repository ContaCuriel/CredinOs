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
        // --- NUEVOS CAMPOS DE REGLAS ---
        'aplicar_reglas_avanzadas',
        'tolerancia_minutos',
        'retardo_menor_minutos_inicio',
        'retardo_menor_minutos_fin',
        'retardos_para_falta',
        'medio_dia_minutos_inicio',
        'medio_dia_minutos_fin',
        'falta_minutos_inicio',
        'castigo_falta_lun_vie',
        'castigo_falta_mar_jue_sab'
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_horario', 'id_horario');
    }
}