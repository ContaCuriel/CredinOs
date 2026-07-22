<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaRayaDetalle extends Model
{
    use HasFactory;

    protected $table = 'lista_raya_detalles';
    protected $primaryKey = 'id_detalle_lista';

    protected $fillable = [
        'id_periodo_lista',
        'id_empleado',
        'sueldo_mensual_historico',
        'sueldo_diario_historico',
        'puesto_historico',
        'dias_periodo',
        'faltas_directas',
        'retardos_acumulados',
        'faltas_por_retardos',
        'descuento_por_faltas',
        'otras_deducciones',
        'percepciones_extra',
        'total_neto'
    ];

    public function periodo()
    {
        return $this->belongsTo(ListaRayaPeriodo::class, 'id_periodo_lista', 'id_periodo_lista');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}