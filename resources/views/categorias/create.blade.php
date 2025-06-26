<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Crear Nueva Categoría</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf
                    @include('categorias._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>