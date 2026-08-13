<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoCuentaDesembolso extends Model
{
    use HasFactory;

    protected $table = 'credito_cuentas_desembolso';

    protected $fillable = [
        'credito_id',
        'banco',
        'titular',
        'numero_cuenta'
    ];

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'credito_id', 'id');
    }
}