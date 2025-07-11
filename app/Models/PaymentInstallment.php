<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'credito_id',
        'numero_pago',
        'monto_pago',
        'monto_capital',
        'monto_interes',
        'fecha_vencimiento',
        'status',
        'fecha_pago',
    ];
}