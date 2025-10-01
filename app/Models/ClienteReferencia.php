<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteReferencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'nombre_referencia',
        'parentesco',
        'telefono',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id_cliente');
    }
}