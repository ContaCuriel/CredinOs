<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PeriodoVacacional;
use App\Models\DeduccionEmpleado; // <-- LÍNEA AÑADIDA
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Asistencia;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'nombre_completo',
        'nombre_fiscal',
        'id_puesto',
        'id_sucursal',
        'status',
        'fecha_ingreso',
        'fecha_nacimiento',
        'nacionalidad',
        'sexo',
        'estado_civil',
        'direccion',
        'cp_fiscal',
        'regimen_fiscal',
        'telefono',
        'curp',
        'rfc',
        'nss',
        'cuenta_bancaria',
        'banco',
        'contacto_emerg_nombre',
        'contacto_emerg_telefono',
        'info_cartas_recomendacion',
        'fecha_baja',
        'motivo_baja',
        'estado_imss',
        'fecha_alta_imss',
        'fecha_baja_imss',
        'id_patron_imss',
        'id_horario',
        'finiquito_firmado_path',
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
    
    // ▼▼▼ FUNCIÓN AÑADIDA ▼▼▼
    /**
     * Define la relación: Un empleado puede tener muchas deducciones.
     * Esta relación solo trae las deducciones que tienen el estatus 'Activo'.
     */
    public function deduccionesActivas()
    {
        return $this->hasMany(DeduccionEmpleado::class, 'id_empleado', 'id_empleado')
                    ->where('status', 'Activo');
    }
    // ▲▲▲ FIN DE LA FUNCIÓN AÑADIDA ▲▲▲
    /**
     * Relación con las asistencias del empleado
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_empleado', 'id_empleado');
    }

    public function getVacacionesDetallado(Carbon $fechaCorte): array
{
    if (!$this->fecha_ingreso) {
        return ['saldo_anterior' => 0, 'proporcional_actual' => 0, 'total_a_pagar' => 0];
    }

    $fechaIngreso = Carbon::parse($this->fecha_ingreso)->startOfDay();
    $fechaCorte = $fechaCorte->copy()->startOfDay();
    
    // 1. Años completos transcurridos
    $anosCompletos = (int) $fechaIngreso->diffInYears($fechaCorte);

    // 2. Cálculo de Saldo Anterior (Años ya cerrados)
    $diasGanadosPasados = 0;
    for ($i = 1; $i <= $anosCompletos; $i++) {
        $diasGanadosPasados += $this->getDiasVacacionesParaAnoDeServicio($i);
    }
    
    $totalTomados = \App\Models\PeriodoVacacional::where('id_empleado', $this->id_empleado)->sum('dias_tomados');
    $saldoAnterior = $diasGanadosPasados - $totalTomados;

    // 3. CÁLCULO PROPORCIONAL (Año en curso)
    // El aniversario de este año fue:
    $ultimoAniversario = $fechaIngreso->copy()->addYears($anosCompletos);
    
    // Días que han pasado desde el aniversario hasta la fecha de corte
    $diasTranscurridos = $ultimoAniversario->diffInDays($fechaCorte);
    
    // Días que le corresponden por el año que está cursando (el siguiente al completo)
    $diasDerechoAnoActual = $this->getDiasVacacionesParaAnoDeServicio($anosCompletos + 1);
    
    // Proporcional: (Días de derecho / 365 días del año) * días trabajados
    $proporcional = ($diasDerechoAnoActual / 365) * $diasTranscurridos;

    return [
        'saldo_anterior' => round($saldoAnterior, 2),
        'proporcional_actual' => round($proporcional, 2),
        'total_a_pagar' => round(max(0, $saldoAnterior + $proporcional), 2)
    ];
}

    /**
     * Función auxiliar que devuelve los días de vacaciones por ley.
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

        return 32;
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }
}