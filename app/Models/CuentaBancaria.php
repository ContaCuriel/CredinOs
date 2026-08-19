<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaBancaria extends Model
{
    use HasFactory;

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'banco',
        'titular',
        'numero_cuenta',
        'clabe',
        'activa'
    ];

    protected $casts = [
        'activa' => 'boolean'
    ];

    // Relación: Una cuenta bancaria puede estar asignada a muchos créditos para recibir pagos
    public function creditos()
    {
        return $this->belongsToMany(Credito::class, 'credito_cuentas_pago', 'cuenta_bancaria_id', 'credito_id');
    }
}