<x-app-layout>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actualizar Logo para: {{ $patron->razon_social }}</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('patrones.logo.update', $patron->id_patron) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3 text-center">
                                <p class="mb-1">Logo Actual:</p>
                                @if ($patron->logo_path)
                                    <img src="{{ asset('storage/' . $patron->logo_path) }}" alt="Logo Actual" class="img-thumbnail" style="max-height: 120px;">
                                @else
                                    <p class="text-muted">No hay un logo actual.</p>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="logo_path" class="form-label">Seleccionar Nuevo Logo</label>
                                <input class="form-control" type="file" id="logo_path" name="logo_path" accept="image/png, image/jpeg, image/gif" required>
                                <div class="form-text">Sube una imagen para el logo (Formatos: PNG, JPG, GIF. Tamaño máx: 2MB).</div>
                            </div>

                            <hr>
                            <div class="text-end">
                                <a href="{{ route('patrones.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Subir y Guardar Logo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
