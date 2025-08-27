<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';
    protected $primaryKey = 'id_sucursal';

    protected $fillable = [
       'nombre_sucursal',
        'calle',
        'numero',
        'colonia',
        'municipio',
        'estado',
        'status',
    ];

    public function empleados()
    {
       return $this->hasMany(Empleado::class, 'id_sucursal', 'id_sucursal');
    }
}