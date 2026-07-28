<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaCierre extends Model
{
    use HasFactory;

    // Conexión obligatoria para tu sistema
    protected $connection = 'tenant';
    
    protected $table = 'asistencia_cierres';
    protected $primaryKey = 'id_asistencia_cierre';

    protected $fillable = [
        'id_empleado',
        'id_sucursal',
        'periodo',
        'faltas',
        'retardos'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }
}