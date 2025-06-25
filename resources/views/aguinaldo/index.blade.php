<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Cálculo de Aguinaldo Anual</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('aguinaldo.calcular') }}" method="POST">
                    @csrf  {{-- Medida de seguridad de Laravel --}}

                    {{-- Usamos la clase 'mb-3' de Bootstrap para el espaciado --}}
                    <div class="mb-3">
                        <label for="fecha_fin_anio" class="form-label">Fecha de Fin de Año:</label>
                        {{-- Usamos la clase 'form-control' para que el input tome el estilo del sistema --}}
                        <input type="date" class="form-control" id="fecha_fin_anio" name="fecha_fin_anio" value="{{ date('Y-12-31') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="dias_aguinaldo" class="form-label">Días de Aguinaldo a Pagar:</label>
                        <input type="number" class="form-control" id="dias_aguinaldo" name="dias_aguinaldo" value="15" required>
                    </div>

                    {{-- Usamos las clases 'btn btn-primary' para que el botón se vea como los del sistema --}}
                    <button type="submit" class="btn btn-primary mt-2">Calcular y Generar Reporte</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>