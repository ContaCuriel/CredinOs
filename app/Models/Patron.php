<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patron extends Model
{
    use HasFactory;

    protected $table = 'patrones';
    protected $primaryKey = 'id_patron';

    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'tipo_persona',
        'rfc',
        'direccion_fiscal',
        'actividad_principal',
        'representante_legal',
        'logo_path',
    ];

    // Relación: Un Patrón puede tener muchos Contratos
    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'patron_id', 'id_patron');
    }

    public function empleados()
    {
        // El primer argumento es el modelo relacionado.
        // El segundo es la clave foránea en la tabla 'empleados' (id_patron_imss).
        // El tercero es la clave local en la tabla 'patrones' (id_patron).
        return $this->hasMany(Empleado::class, 'id_patron_imss', 'id_patron');
    }
}