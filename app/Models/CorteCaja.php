<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    use HasFactory;

    protected $table = 'cortes_caja';

    protected $fillable = [
        'caja_id', 
        'usuario_id', 
        'fecha_apertura', 
        'fecha_cierre',
        'saldo_inicial', 
        'ingresos', 
        'egresos', 
        'saldo_teorico',
        'saldo_fisico', 
        'estatus'
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function transacciones()
    {
        return $this->hasMany(TransaccionCaja::class, 'corte_caja_id');
    }
}