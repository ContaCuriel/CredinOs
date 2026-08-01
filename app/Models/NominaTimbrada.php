<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaTimbrada extends Model
{
    use HasFactory;

    protected $table = 'nominas_timbradas';
    protected $primaryKey = 'id_nomina_timbrada';

    protected $fillable = [
        'id_detalle_lista',
        'id_empleado',
        'sueldo_bruto',
        'isr_retenido',
        'imss_retenido',
        'estado_timbrado',
        'uuid_cfdi',
        'facturama_id',
        'xml_path',
        'pdf_path',
        'mensaje_error_sat'
    ];

    public function detalleListaRaya()
    {
        return $this->belongsTo(ListaRayaDetalle::class, 'id_detalle_lista', 'id_detalle_lista');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}