<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'asistencias';

    /**
     * The primary key associated with the table.
     * (Laravel asume 'id' por defecto, así que esto es opcional si usaste $table->id())
     *
     * @var string
     */
    // protected $primaryKey = 'id_asistencia'; // Solo si nombraste tu PK diferente a 'id'

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_empleado',
        'fecha',
        'hora_llegada',
        'status_asistencia',
        'notas_incidencia',
    ];

    /**
     * The attributes that should be cast.
     * Esto le dice a Laravel que trate el campo 'fecha' como un objeto Carbon.
     *
     * @var array
     */
    protected $casts = [
        'fecha' => 'date',
        // 'hora_llegada' podría ser 'datetime:H:i:s' o 'time' si solo guardas la hora
        // pero para input tipo TIME, dejarlo como string y castearlo al usarlo suele ser simple.
    ];

    /**
     * Define la relación: Un registro de Asistencia pertenece a un Empleado.
     */
    public function empleado()
    {
        // Asegúrate que los nombres de las llaves coincidan con tu tabla empleados
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}