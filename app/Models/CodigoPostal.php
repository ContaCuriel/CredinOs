<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoPostal extends Model
{
    use HasFactory;

    /**
     * La conexión de base de datos que debe ser utilizada por el modelo.
     * Forzamos a que siempre use la conexión 'pgsql' (la central/landlord).
     *
     * @var string
     */
    protected $connection = 'pgsql';
    
    // Si no tienes un $fillable, es bueno añadirlo.
    protected $fillable = [
        'codigo_postal',
        'colonia',
        'municipio',
        'estado',
    ];
}