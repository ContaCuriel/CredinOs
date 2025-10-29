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
        // Campos que ya tenías
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
        'ocupacion',
        'nombre_negocio',
        'giro_negocio',
        'antiguedad_negocio',
        'ingresos_mensuales',
        'gastos_mensuales',

        // Nuevos campos de la migración
        'fecha_nacimiento',
        'genero',
        'vencimiento_ine',
        'estado_nacimiento',
        'nacionalidad',
        'estado_civil',
        'numero_hijos',
        'dependientes_economicos',
        'fecha_comprobante_domicilio',
        'destino_credito',
        'telefono_fijo', // <-- AÑADIR
        'anios_domicilio', // <-- AÑADIR
        'tipo_vivienda', // <-- AÑADIR
    ];

    // Relación con Sucursal
    public function sucursal()
{
    return $this->belongsTo(Sucursal::class, 'id_sucursal')
                ->withDefault([
                    'nombre_sucursal' => 'Sin Asignar'
                ]);
}

    public function groups()
{
    return $this->belongsToMany(Group::class, 'client_group', 'client_id', 'group_id');
}

public function creditos()
{
    return $this->morphMany(Credito::class, 'loanable');
}



}