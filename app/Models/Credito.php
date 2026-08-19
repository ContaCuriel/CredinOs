<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';

    // ¡AQUÍ ESTÁ LA MAGIA! Le decimos a Laravel el nombre real
    protected $primaryKey = 'id_credito';

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
        'estatus',
        'fecha_solicitud',
        'fecha_aprobacion',
        'fecha_desembolso',
        'fecha_primer_pago',
        'fecha_vencimiento',
        'asesor_id'
    ];

    protected $casts = [
        'monto_solicitado' => 'float',
        'monto_aprobado' => 'float',
        'tasa_interes_aplicada' => 'float',
        'comision_apertura_aplicada' => 'float',
        'fecha_solicitud' => 'date',
        'fecha_aprobacion' => 'date',
        'fecha_desembolso' => 'date',
        'fecha_primer_pago' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    // Relación con el Cliente (Si es individual)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id_cliente');
    }

    // Relación con el Grupo (Si es grupal)
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id', 'id');
    }

    // Relación con el Producto de Crédito
    public function producto()
    {
        return $this->belongsTo(ProductoCredito::class, 'producto_id', 'id');
    }

    // Relación con el Asesor que originó el crédito
    public function asesor()
    {
        return $this->belongsTo(Empleado::class, 'asesor_id', 'id_empleado');
    }

    // Integrantes del crédito (Tabla pivote)
    public function integrantes()
    {
        // Se le especifica 'id_credito' y 'id_cliente' para que no mande nulls
        return $this->belongsToMany(Cliente::class, 'credito_clientes', 'credito_id', 'cliente_id', 'id_credito', 'id_cliente')
                    ->withPivot('es_lider', 'monto_individual')
                    ->withTimestamps();
    }

    // Cuentas bancarias de desembolso
    public function cuentasDesembolso()
    {
        // AQUÍ ESTÁ LA SOLUCIÓN: Cambiamos 'id' por 'id_credito'
        return $this->hasMany(CreditoCuentaDesembolso::class, 'credito_id', 'id_credito');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id_sucursal');
    }

    public function garantia() 
    { 
        // AQUÍ ESTÁ LA SOLUCIÓN: Cambiamos 'id' por 'id_credito'
        return $this->hasOne(CreditoGarantia::class, 'credito_id', 'id_credito'); 
    }