<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditoGarantia extends Model
{
    protected $table = 'credito_garantias';

    // ¡Súper importante para que Laravel no ignore los datos al guardar!
    protected $fillable = [
        'credito_id',
        'tipo_garantia',
        'vehiculo_documento',
        'vehiculo_tipo',
        'vehiculo_marca',
        'vehiculo_modelo',
        'vehiculo_anio',
        'vehiculo_motor',
        'vehiculo_color',
        'vehiculo_serie',
        'propiedad_documento',
        'propiedad_ubicacion',
        'propiedad_medidas',
        'propiedad_superficie',
        'estatus_resguardo',
        'ubicacion_fisica',
        'fecha_devolucion',
        'notas_resguardo'
    ];

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'credito_id', 'id');
    }
}