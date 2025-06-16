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
}