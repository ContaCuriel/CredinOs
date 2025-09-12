<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Journal;

class Gasto extends Model
{
    use HasFactory;

    // Usaremos guarded para proteger solo el id
    protected $guarded = ['id'];

    // Indicamos que estos campos son fechas
    protected $casts = [
        'fecha_gasto' => 'date',
        'requiere_aprobacion' => 'boolean',
    ];

    // Relaciones (la parte más importante)
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function sucursal()
{
    // 1er parámetro: El modelo al que nos conectamos (Sucursal).
    // 2do parámetro: La llave foránea en ESTA tabla (gastos.sucursal_id).
    // 3er parámetro: La llave primaria en la OTRA tabla (sucursales.id_sucursal).
    return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id_sucursal');
}

    public function usuario()
    {
        // El usuario que registró el gasto
        return $this->belongsTo(User::class, 'usuario_registra_id');
    }

    public function journal()
{
    return $this->morphOne(Journal::class, 'sourceable');
}

}