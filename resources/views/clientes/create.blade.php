<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nuevo Cliente</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading fw-bold">¡Por favor corrige los siguientes errores!</h6>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf

                    {{-- Datos Personales --}}
                    <h6 class="mt-3">Datos Personales</h6>
                    <hr class="mt-0">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nombre" class="form-label">Nombre(s) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="curp" class="form-label">CURP</label>
                            <input type="text" class="form-control" name="curp" value="{{ old('curp') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rfc" class="form-label">RFC</label>
                            <input type="text" class="form-control" name="rfc" value="{{ old('rfc') }}">
                        </div>
                    </div>

                    {{-- Datos de Contacto --}}
                    <h6 class="mt-4">Datos de Contacto</h6>
                    <hr class="mt-0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono_celular" class="form-label">Teléfono Celular</label>
                            <input type="tel" class="form-control" name="telefono_celular" value="{{ old('telefono_celular') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                        </div>
                    </div>

                    {{-- Dirección --}}
                    <h6 class="mt-4">Dirección</h6>
                    <hr class="mt-0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="calle" class="form-label">Calle</label>
                            <input type="text" class="form-control" name="calle" value="{{ old('calle') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" name="numero" value="{{ old('numero') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="codigo_postal" class="form-label">Código Postal</label>
                            <input type="text" class="form-control" name="codigo_postal" value="{{ old('codigo_postal') }}">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="colonia" class="form-label">Colonia</label>
                            <input type="text" class="form-control" name="colonia" value="{{ old('colonia') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="municipio" class="form-label">Municipio</label>
                            <input type="text" class="form-control" name="municipio" value="{{ old('municipio') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" name="estado" value="{{ old('estado') }}">
                        </div>
                    </div>

                    {{-- Datos del Negocio/Trabajo --}}
<h6 class="mt-4">Datos Laborales y Financieros</h6>
<hr class="mt-0">
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="ocupacion" class="form-label">Ocupación</label>
        <input type="text" class="form-control" name="ocupacion" value="{{ old('ocupacion', $cliente->ocupacion ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="nombre_negocio" class="form-label">Nombre del Negocio</label>
        <input type="text" class="form-control" name="nombre_negocio" value="{{ old('nombre_negocio', $cliente->nombre_negocio ?? '') }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="ingresos_mensuales" class="form-label">Ingresos Mensuales ($)</label>
        <input type="number" step="0.01" class="form-control" name="ingresos_mensuales" value="{{ old('ingresos_mensuales', $cliente->ingresos_mensuales ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="gastos_mensuales" class="form-label">Gastos Mensuales ($)</label>
        <input type="number" step="0.01" class="form-control" name="gastos_mensuales" value="{{ old('gastos_mensuales', $cliente->gastos_mensuales ?? '') }}">
    </div>
</div>


                    {{-- Asignación --}}
                    <h6 class="mt-4">Asignación</h6>
                    <hr class="mt-0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_sucursal" required>
                                <option value="" disabled selected>Selecciona una sucursal</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ old('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>