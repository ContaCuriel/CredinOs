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
        'ocupacion',
        'nombre_negocio',
        'giro_negocio',
        'antiguedad_negocio',
        'ingresos_mensuales',
        'gastos_mensuales',
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
        'telefono_fijo',
        'anios_domicilio',
        'tipo_vivienda',
        // --- ¡CAMPOS NUEVOS AGREGADOS AQUÍ! ---
        'mismo_domicilio_laboral',
        'calle_negocio',
        'numero_negocio',
        'colonia_negocio',
        'codigo_postal_negocio',
        'municipio_negocio',
        'estado_negocio',
        'estatus'
    ];

    protected $casts = [
        'id_sucursal'                 => 'integer',
        'antiguedad_negocio'          => 'integer',
        'numero_hijos'                => 'integer',
        'dependientes_economicos'     => 'integer',
        'anios_domicilio'             => 'integer',
        'mismo_domicilio_laboral'     => 'boolean',
        
        // ¡Cambiamos decimal por float para evitar a Brick\Math!
        'ingresos_mensuales'          => 'float',
        'gastos_mensuales'            => 'float',
        
        // Formato estricto de fechas
        'fecha_nacimiento'            => 'date:Y-m-d',
        'fecha_comprobante_domicilio' => 'date:Y-m-d',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal')
                    ->withDefault(['nombre_sucursal' => 'Sin Asignar']);
    }

    public function referencias()
    {
        return $this->hasMany(ClienteReferencia::class, 'cliente_id', 'id_cliente');
    }

    public function grupos()
    {
        return $this->belongsToMany(GrupoSolidario::class, 'grupo_cliente', 'cliente_id', 'grupo_id'); // Ajusta los nombres de tus tablas pivote
    }

   public function creditos()
    {
        return $this->belongsToMany(Credito::class, 'credito_integrantes', 'cliente_id', 'credito_id'); // Ajusta si usas un pivote diferente
    }
}