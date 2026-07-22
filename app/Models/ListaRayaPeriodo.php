<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaRayaPeriodo extends Model
{
    use HasFactory;

    protected $table = 'lista_raya_periodos';
    protected $primaryKey = 'id_periodo_lista';

    protected $fillable = [
        'periodo_rango',
        'id_sucursal',
        'status_periodo'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    public function detalles()
    {
        return $this->hasMany(ListaRayaDetalle::class, 'id_periodo_lista', 'id_periodo_lista');
    }
}