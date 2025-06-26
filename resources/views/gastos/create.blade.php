<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nuevo Gasto</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('gastos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Incluimos el formulario parcial --}}
                    @include('gastos._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>