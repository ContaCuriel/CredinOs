<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Gasto #{{ $gasto->id }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('gastos.update', $gasto) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') {{-- Directiva para indicar que es una actualización --}}
                    
                    {{-- Reutilizamos exactamente el mismo formulario parcial --}}
                    @include('gastos._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>