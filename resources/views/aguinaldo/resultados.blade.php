<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Reporte de Aguinaldo Calculado</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{-- He añadido las clases 'table-bordered' y 'table-striped' para que coincida con el estilo de tu sistema --}}
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre Empleado</th>
                                <th>Puesto</th>
                                <th>Sucursal</th>
                                <th>Fecha Ingreso</th>
                                <th class="text-right">Salario Diario</th>
                                <th class="text-right">Días Trabajados (Año)</th>
                                <th class="text-right">Aguinaldo a Pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($resultados as $resultado)
                                <tr>
                                    <td>{{ $resultado['nombre_completo'] }}</td>
                                    <td>{{ $resultado['nombre_puesto'] }}</td>
                                    <td>{{ $resultado['nombre_sucursal'] }}</td>
                                    <td>{{ date('d/m/Y', strtotime($resultado['fecha_ingreso'])) }}</td>
                                    <td class="text-right">${{ number_format($resultado['salario_diario'], 2) }}</td>
                                    <td class="text-right">{{ $resultado['dias_trabajados'] }}</td>
                                    <td class="text-right">${{ number_format($resultado['aguinaldo_a_pagar'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron empleados que cumplan los criterios.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            {{-- Estilo para la fila de totales, similar al de Bootstrap --}}
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <td colspan="6" class="text-right">TOTAL GENERAL A PAGAR:</td>
                                <td class="text-right">${{ number_format($totalAguinaldoGeneral, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Contenedor para los botones de acción --}}
                <div class="text-center mt-4">
                    <a href="{{ route('aguinaldo.index') }}" class="btn btn-secondary" style="margin-right: 10px;">Realizar Otro Cálculo</a>

                    <form action="{{ route('aguinaldo.exportar') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="fecha_fin_anio" value="{{ request('fecha_fin_anio') }}">
                        <input type="hidden" name="dias_aguinaldo" value="{{ request('dias_aguinaldo') }}">
                        
                        {{-- Botón verde para la acción principal de exportar --}}
                        <button type="submit" class="btn btn-success">Exportar a Excel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>