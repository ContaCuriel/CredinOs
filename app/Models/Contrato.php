<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    protected $table = 'contratos'; // Especifica el nombre de la tabla
    protected $primaryKey = 'id_contrato'; // Especifica la llave primaria

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
protected $fillable = [
        'id_empleado',
        'patron_tipo', // Lo mantenemos por ahora, podría llenarse desde el Patrón seleccionado
        // 'nombre_patron', // Este campo es probable que ya no lo necesites si usas id_patron
        'id_patron',    // <-- NUEVO
        'tipo_contrato',
        'fecha_inicio',
        'fecha_fin',
        'ruta_contrato_firmado',
        // ... otros campos si los tienes ...
    ];

    /**
     * The attributes that should be cast.
     * Para que Laravel maneje las fechas como objetos Carbon automáticamente.
     * @var array
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Define la relación: Un Contrato pertenece a un Empleado.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }

// =====> NUEVA RELACIÓN <=====
    public function patron()
    {
        return $this->belongsTo(Patron::class, 'id_patron', 'id_patron');
    }
    // ===========================




}