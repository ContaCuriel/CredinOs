<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';

    // ¡ELIMINADO el override de primaryKey porque la base de datos SÍ usa 'id'!

    protected $fillable = [
        'folio',
        'sucursal_id',
        'nombre_credito',
        'cliente_id',
        'grupo_id',
        'producto_id',
        'monto_solicitado',
        'plazo_solicitado',
        'monto_aprobado',
        'plazo_aprobado',
        'tasa_interes_aplicada',
        'comision_apertura_aplicada',
        'retencion_seguro_aplicada', // <-- Agregado para guardar el cobro del seguro
        'estatus',
        'fecha_solicitud',
        'fecha_aprobacion',
        'fecha_desembolso',
        'fecha_primer_pago',
        'fecha_vencimiento',
        'asesor_id',
        'patron_id' // <-- Agregado para guardar a la empresa emisora
    ];

    protected $casts = [
        'monto_solicitado' => 'float',
        'monto_aprobado' => 'float',
        'tasa_interes_aplicada' => 'float',
        'comision_apertura_aplicada' => 'float',
        'retencion_seguro_aplicada' => 'float', // <-- Cast a decimal
        'fecha_solicitud' => 'date',
        'fecha_aprobacion' => 'date',
        'fecha_desembolso' => 'date',
        'fecha_primer_pago' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id_cliente');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(ProductoCredito::class, 'producto_id', 'id');
    }

    public function asesor()
    {
        return $this->belongsTo(Empleado::class, 'asesor_id', 'id_empleado');
    }

    // Integrantes del crédito (Regresado a su estado original limpio)
    public function integrantes()
    {
        return $this->belongsToMany(Cliente::class, 'credito_clientes', 'credito_id', 'cliente_id')
                    ->withPivot('es_lider', 'monto_individual')
                    ->withTimestamps();
    }

    // Cuentas bancarias de desembolso (Regresado a 'id')
    public function cuentasDesembolso()
    {
        return $this->hasMany(CreditoCuentaDesembolso::class, 'credito_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id_sucursal');
    }

    // Garantía (Regresado a 'id')
    public function garantia() 
    { 
        return $this->hasOne(CreditoGarantia::class, 'credito_id', 'id'); 
    }

    // Relación con la Empresa Emisora (Patrón)
    public function patron()
    {
        return $this->belongsTo(Patron::class, 'patron_id', 'id_patron');
    }

    // Dónde puede pagar el cliente (Cuentas de banco de la empresa)
    public function cuentasParaPago()
    {
        return $this->belongsToMany(CuentaBancaria::class, 'credito_cuentas_pago', 'credito_id', 'cuenta_bancaria_id');
    }

    // Dónde puede pagar el cliente (Sucursales físicas / Cajas)
    public function sucursalesParaPago()
    {
        return $this->belongsToMany(Sucursal::class, 'credito_sucursales_pago', 'credito_id', 'sucursal_id');
    }
}