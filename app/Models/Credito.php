<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';
    protected $primaryKey = 'id_credito';

    protected $fillable = [
         'reference_number',
        'loanable_id',
        'loanable_type',
        'id_sucursal',
        'id_asesor',
        'monto_solicitado',
        'monto_autorizado',
        'plazo',
        'frecuencia_pago',
        'tasa_interes',
        'fecha_solicitud',
        'fecha_desembolso',
        'status',
    ];

    /**
     * Relación Polimórfica: Un crédito puede pertenecer a un Cliente o a un Grupo.
     */
    public function loanable()
    {
        return $this->morphTo();
    }

    /**
     * Relación: Un crédito pertenece a una sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    /**
     * Relación: Un crédito es atendido por un asesor (Usuario).
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'id_asesor', 'id');
    }


    // Dentro de la clase Credito, añade este método
public function paymentInstallments()
{
    return $this->hasMany(PaymentInstallment::class, 'credito_id', 'id_credito');
}

public function members()
{
    return $this->belongsToMany(Cliente::class, 'credito_cliente', 'credito_id', 'cliente_id')
                ->withPivot('individual_amount')
                ->withTimestamps();
}

}