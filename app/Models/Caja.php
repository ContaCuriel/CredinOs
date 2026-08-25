<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';

    protected $fillable = [
        'sucursal_id', 
        'nombre', 
        'estatus', 
        'saldo_actual'
    ];

    public function cortes()
    {
        return $this->hasMany(CorteCaja::class, 'caja_id');
    }
}