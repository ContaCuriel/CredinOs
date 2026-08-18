<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaTimbrada extends Model
{
    use HasFactory;

    protected $table = 'nominas_timbradas';

    // ¡AQUÍ ESTÁ LA SOLUCIÓN! Le decimos a Laravel cómo se llama tu ID real
    protected $primaryKey = 'id_nomina_timbrada';

    protected $guarded = [];

    /**
     * Relación con el detalle de la lista de raya
     */
    public function detalle()
    {
        return $this->belongsTo(ListaRayaDetalle::class, 'id_detalle_lista', 'id_detalle_lista');
    }

    /**
     * Relación directa con el empleado
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}