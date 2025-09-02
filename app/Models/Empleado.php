<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PeriodoVacacional;
use App\Models\DeduccionEmpleado;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'nombre_completo', 'id_puesto', 'id_sucursal', 'status', 'fecha_ingreso', 'fecha_nacimiento',
        'nacionalidad', 'sexo', 'estado_civil', 'direccion', 'telefono', 'curp', 'rfc', 'nss',
        'cuenta_bancaria', 'banco', 'contacto_emerg_nombre', 'contacto_emerg_telefono',
        'info_cartas_recomendacion', 'fecha_baja', 'motivo_baja', 'estado_imss', 'fecha_alta_imss',
        'fecha_baja_imss', 'id_patron_imss', 'id_horario', 'finiquito_firmado_path',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_nacimiento' => 'date',
        'fecha_baja' => 'date',
        'fecha_alta_imss' => 'date',
        'fecha_baja_imss' => 'date',
    ];

    // --- RELACIONES ---

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'id_puesto', 'id_puesto');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_empleado', 'id_empleado');
    }

    public function ultimoContrato()
    {
        return $this->hasOne(Contrato::class, 'id_empleado', 'id_empleado')->latest('fecha_fin');
    }

    public function patronImss()
    {
        return $this->belongsTo(Patron::class, 'id_patron_imss', 'id_patron');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
    }
    
    public function deduccionesActivas()
    {
        return $this->hasMany(DeduccionEmpleado::class, 'id_empleado', 'id_empleado')
                    ->where('status', 'Activo');
    }

    // --- INICIO DE LA CORRECCIÓN ---
    /**
     * Calcula de forma precisa el saldo de vacaciones de un empleado hasta una fecha de corte específica.
     * Esta versión es robusta y maneja correctamente a empleados con menos de un año de servicio.
     *
     * @param Carbon $fechaCorte La fecha hasta la que se calcularán las vacaciones.
     * @return array
     */
    public function getDetalleVacaciones(Carbon $fechaCorte): array
{
    if (!$this->fecha_ingreso || $fechaCorte->isBefore($this->fecha_ingreso)) {
        return [
            'total_devengado' => 0.0,
            'total_tomado' => 0.0,
            'saldo_final' => 0.0,
            'proporcional_ultimo_periodo' => 0.0,
        ];
    }

    $fechaIngreso = Carbon::parse($this->fecha_ingreso);
    $anosCompletos = $fechaIngreso->diffInYears($fechaCorte);
    $diasPorAnosCompletos = 0.0;

    // 1. Sumar los días correspondientes a cada AÑO COMPLETO de servicio.
    for ($i = 1; $i <= $anosCompletos; $i++) {
        $diasPorAnosCompletos += $this->getDiasVacacionesParaAnoDeServicio($i);
    }

    // 2. Calcular los días PROPORCIONALES del último año de servicio (el que está "en curso" o fue el final).
    $inicioUltimoPeriodo = $fechaIngreso->copy()->addYears($anosCompletos);
    $diasTrabajadosUltimoPeriodo = $fechaCorte->diffInDays($inicioUltimoPeriodo) + 1;
    $anoCorrespondienteUltimoPeriodo = $anosCompletos + 1;
    $diasDerechoUltimoPeriodo = $this->getDiasVacacionesParaAnoDeServicio($anoCorrespondienteUltimoPeriodo);
    
    $diasProporcionales = 0.0;
    if ($diasTrabajadosUltimoPeriodo > 0) {
        $diasProporcionales = ($diasDerechoUltimoPeriodo / 365.0) * $diasTrabajadosUltimoPeriodo;
    }

    // 3. Calcular los totales.
    $totalDevengado = $diasPorAnosCompletos + $diasProporcionales;
    $totalTomados = (float) PeriodoVacacional::where('id_empleado', $this->id_empleado)->sum('dias_tomados');
    $saldoFinal = $totalDevengado - $totalTomados;

    return [
        'total_devengado' => $totalDevengado,
        'total_tomado' => $totalTomados,
        'saldo_final' => $saldoFinal,
        'proporcional_ultimo_periodo' => $diasProporcionales,
    ];
}

/**
 * Función auxiliar que devuelve los días de vacaciones por ley (LFT).
 */
public function getDiasVacacionesParaAnoDeServicio(int $anoDeServicio): int
{
    if ($anoDeServicio < 1) return 0;
    if ($anoDeServicio == 1) return 12;
    if ($anoDeServicio == 2) return 14;
    if ($anoDeServicio == 3) return 16;
    if ($anoDeServicio == 4) return 18;
    if ($anoDeServicio == 5) return 20;
    if ($anoDeServicio >= 6 && $anoDeServicio <= 10) return 22;
    if ($anoDeServicio >= 11 && $anoDeServicio <= 15) return 24;
    if ($anoDeServicio >= 16 && $anoDeServicio <= 20) return 26;
    if ($anoDeServicio >= 21 && $anoDeServicio <= 25) return 28;
    if ($anoDeServicio >= 26 && $anoDeServicio <= 30) return 30;
    if ($anoDeServicio >= 31) return 32;

    return 32; // Default para años muy altos
}
}