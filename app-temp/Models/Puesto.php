<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    use HasFactory;
    protected $table = 'puestos';
    protected $primaryKey = 'id_puesto';

protected $fillable = [
    'nombre_puesto',
    // 'descripcion_puesto', // <--- LÍNEA ELIMINADA O COMENTADA
    'salario_mensual',
];

}