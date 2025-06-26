<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;


    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    // --- AÑADE ESTA LÍNEA ---
    protected $table = 'proveedores';
    // -----------------------
    

    protected $fillable = [
        'nombre',
    ];

    // Un proveedor puede tener muchos gastos
    public function gastos()
    {
        return $this->hasMany(Gasto::class);
    }
}