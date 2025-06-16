<?php

namespace App\Exports;

use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ContratosPanoramaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search_nombre_empleado;
    protected $id_sucursal_filter;

    // Constructor para pasar los filtros desde el controlador
    public function __construct($search_nombre_empleado = null, $id_sucursal_filter = null)
    {
        $this->search_nombre_empleado = $search_nombre_empleado;
        $this->id_sucursal_filter = $id_sucursal_filter;
    }

    /**
    * Define la consulta para obtener los datos a exportar.
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        // Replicamos la consulta del ContratoController@index
        $query = Empleado::query()->where('status', 'Alta')
            ->with(['puesto', 'sucursal', 'ultimoContrato']) // Aseguramos cargar las relaciones
            ->withCount('contratos');

        if (!empty($this->search_nombre_empleado)) {
            $query->where('nombre_completo', 'like', '%' . $this->search_nombre_empleado . '%');
        }

        if (!empty($this->id_sucursal_filter)) {
            $query->where('id_sucursal', $this->id_sucursal_filter);
        }

        return $query->orderBy('nombre_completo', 'asc');
    }

    /**
    * Define las cabeceras de las columnas en el Excel.
    * @return array
    */
    public function headings(): array
    {
        return [
            'Empleado',
            'Puesto',
            'Sucursal',
            'Antigüedad Empleado',
            'Tipo Últ. Contrato',
            'Inicio Últ. Contrato',
            'Fin Últ. Contrato',
            'Duración Últ. Contrato',
            'Nº Contratos (Total)',
        ];
    }

    /**
    * Mapea cada fila de datos (cada objeto Empleado) al formato del Excel.
    * @param mixed $empleado El objeto Empleado recuperado por la consulta.
    * @return array
    */
    public function map($empleado): array
    {
        $antiguedad = 'N/A';
        if ($empleado->fecha_ingreso) {
            // Aseguramos que fecha_ingreso sea un objeto Carbon
            $fechaIngreso = ($empleado->fecha_ingreso instanceof Carbon) ? $empleado->fecha_ingreso : Carbon::parse($empleado->fecha_ingreso);
            $antiguedad = $fechaIngreso->diffForHumans(null, true, false, 2);
        }

        $tipoUltContrato = $empleado->ultimoContrato ? $empleado->ultimoContrato->tipo_contrato : 'N/A';

        $inicioUltContrato = 'N/A';
        if ($empleado->ultimoContrato && $empleado->ultimoContrato->fecha_inicio) {
            $fechaInicioUltContrato = ($empleado->ultimoContrato->fecha_inicio instanceof Carbon) ? $empleado->ultimoContrato->fecha_inicio : Carbon::parse($empleado->ultimoContrato->fecha_inicio);
            $inicioUltContrato = $fechaInicioUltContrato->format('d/m/Y');
        }

        $finUltContrato = 'N/A';
        if ($empleado->ultimoContrato && $empleado->ultimoContrato->fecha_fin) {
            $fechaFinUltContrato = ($empleado->ultimoContrato->fecha_fin instanceof Carbon) ? $empleado->ultimoContrato->fecha_fin : Carbon::parse($empleado->ultimoContrato->fecha_fin);
            $finUltContrato = $fechaFinUltContrato->format('d/m/Y');
        }

        $duracionUltContrato = 'N/A';
        if ($empleado->ultimoContrato && $empleado->ultimoContrato->fecha_inicio && $empleado->ultimoContrato->fecha_fin) {
            // Aseguramos que ambas fechas sean Carbon para diffForHumans
            $fechaInicio = ($empleado->ultimoContrato->fecha_inicio instanceof Carbon) ? $empleado->ultimoContrato->fecha_inicio : Carbon::parse($empleado->ultimoContrato->fecha_inicio);
            $fechaFin = ($empleado->ultimoContrato->fecha_fin instanceof Carbon) ? $empleado->ultimoContrato->fecha_fin : Carbon::parse($empleado->ultimoContrato->fecha_fin);
            $duracionUltContrato = $fechaInicio->diffForHumans($fechaFin, true, false, 2);
        }

        return [
            $empleado->nombre_completo,
            $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A',
            $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'N/A',
            $antiguedad,
            $tipoUltContrato,
            $inicioUltContrato,
            $finUltContrato,
            $duracionUltContrato,
            $empleado->contratos_count ?? 0, // Usamos el count que ya viene de la consulta
        ];
    }
}