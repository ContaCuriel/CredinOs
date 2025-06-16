<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Cambiado el nombre de la clase a DeduccionEmpleado
class DeduccionEmpleado extends Model
{
    use HasFactory;

    // La tabla ahora se llama 'deducciones_empleados'
    protected $table = 'deducciones_empleados';

    // La llave primaria sigue siendo 'id' por defecto
    // protected $primaryKey = 'id';

    // Ajustamos los campos que se pueden llenar masivamente
    protected $fillable = [
        'id_empleado',
        'tipo_deduccion',
        'descripcion',
        'monto_quincenal',
        'status',
        'fecha_solicitud',
        // Campos de Préstamo
        'monto_total_prestamo',
        'plazo_quincenas',
        'quincenas_pagadas',
        'saldo_pendiente',
        // Nuevo campo para Caja de Ahorro
        'monto_acumulado', // <-- AÑADIDO
];

    // Ajustamos los casts
    protected $casts = [
        'fecha_solicitud' => 'date',
        'monto_total_prestamo' => 'decimal:2',
        'monto_quincenal' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'monto_acumulado' => 'decimal:2', // <-- AÑADIDO
    ];

    /**
     * Relación: Una deducción pertenece a un Empleado.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}