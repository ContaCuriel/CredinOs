<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
    'nombre',
    'apellido_paterno',
    'apellido_materno',
    'curp',
    'rfc',
    'telefono_celular',
    'email',
    'calle',
    'numero',
    'colonia',
    'codigo_postal',
    'municipio',
    'estado',
    'id_sucursal',
    // --- NUEVOS CAMPOS ---
    'ocupacion',
    'nombre_negocio',
    'giro_negocio',
    'antiguedad_negocio',
    'ingresos_mensuales',
    'gastos_mensuales',
];

    // Relación con Sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    public function groups()
{
    return $this->belongsToMany(Group::class, 'client_group', 'client_id', 'group_id');
}



}