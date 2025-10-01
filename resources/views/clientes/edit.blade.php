¡Perfecto\! Aquí tienes los archivos Blade para las vistas `index` y `edit`, consistentes con el controlador que te proporcioné.

### **1. Vista de Lista de Clientes (`index.blade.php`)**

Esta versión mejora tu `index` original añadiendo:

  * Un espacio para **alertas de éxito/error**.
  * Una columna de **"Estatus"** como preparación para la lógica de créditos.
  * Botones de acción más claros (Ver, Editar, Eliminar).
  * Manejo del caso en que no haya clientes registrados (`@forelse`).

**Ruta: `resources/views/clientes/index.blade.php`**

```html
<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Clientes</h5>
                <a href="{{ route('clientes.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Cliente
                </a>
            </div>
            <div class="card-body">
                {{-- Alertas de Sesión --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Nombre Completo</th>
                                <th scope="col">Contacto</th>
                                <th scope="col">Estatus</th>
                                <th scope="col">Sucursal</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->nombre_completo }}</td>
                                    <td>
                                        {{ $cliente->telefono_celular ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">{{ $cliente->email ?? 'Sin correo' }}</small>
                                    </td>
                                    <td>
                                        {{-- Placeholder para el futuro estatus del cliente --}}
                                        <span class="badge bg-secondary">Sin créditos</span>
                                    </td>
                                    <td>{{ $cliente->sucursal->nombre_sucursal ?? 'Sin asignar' }}</td>
                                    <td class="text-end">
                                        {{-- Botón para Ver (opcional, si creas una vista show) --}}
                                        {{-- <a href="{{ route('clientes.show', $cliente->id_cliente) }}" class="btn btn-info btn-sm" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </a> --}}
                                        
                                        {{-- Botón para Editar --}}
                                        <a href="{{ route('clientes.edit', $cliente->id_cliente) }}" class="btn btn-warning btn-sm" title="Editar Cliente">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>
                                        
                                        {{-- Formulario para Eliminar --}}
                                        <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar Cliente" onclick="return confirm('¿Estás seguro de que quieres eliminar a este cliente?')">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay clientes registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Enlaces de Paginación --}}
                <div class="mt-3">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

-----

### **2. Vista de Edición de Cliente (`edit.blade.php`)**

Esta es la implementación del formulario de acordeón para la edición. La clave aquí es el uso de la función `old('campo', $cliente->campo)` en cada `input`. Esto hace que:

1.  Al cargar la página, se muestren los datos actuales del cliente (`$cliente->campo`).
2.  Si la validación falla y la página se recarga, se muestren los datos que el usuario ya había corregido (`old('campo')`).

**Ruta: `resources/views/clientes/edit.blade.php`**

```html
<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Cliente: {{ $cliente->nombre_completo }}</h5>
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

                <form action="{{ route('clientes.update', $cliente->id_cliente) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="accordion" id="accordionCliente">

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <strong>Sección 1: Datos Personales</strong>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Nombre(s) <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Paterno <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellido_paterno" value="{{ old('apellido_paterno', $cliente->apellido_paterno) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Materno <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno', $cliente->apellido_materno) }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento) }}" required></div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Género <span class="text-danger">*</span></label>
                                            <select name="genero" class="form-select" required>
                                                <option value="Masculino" {{ old('genero', $cliente->genero) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                                <option value="Femenino" {{ old('genero', $cliente->genero) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                                <option value="Otro" {{ old('genero', $cliente->genero) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3"><label class="form-label">CURP</label><input type="text" class="form-control text-uppercase" name="curp" value="{{ old('curp', $cliente->curp) }}"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Vencimiento del INE <span class="text-danger">*</span></label><input type="date" class="form-control" name="vencimiento_ine" value="{{ old('vencimiento_ine', $cliente->vencimiento_ine) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Estado de Nacimiento <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado_nacimiento" value="{{ old('estado_nacimiento', $cliente->estado_nacimiento) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Nacionalidad <span class="text-danger">*</span></label><input type="text" class="form-control" name="nacionalidad" value="{{ old('nacionalidad', $cliente->nacionalidad) }}" required></div>
                                    </div>
                                    <div class="row">
                                         <div class="col-md-4 mb-3"><label class="form-label">Estado Civil <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado_civil" value="{{ old('estado_civil', $cliente->estado_civil) }}" required></div>
                                         <div class="col-md-4 mb-3"><label class="form-label">Número de Hijos <span class="text-danger">*</span></label><input type="number" class="form-control" name="numero_hijos" value="{{ old('numero_hijos', $cliente->numero_hijos) }}" min="0" required></div>
                                         <div class="col-md-4 mb-3"><label class="form-label">Dependientes Económicos <span class="text-danger">*</span></label><input type="number" class="form-control" name="dependientes_economicos" value="{{ old('dependientes_economicos', $cliente->dependientes_economicos) }}" min="0" required></div>
                                    </div>
                                    <hr>
                                    <h6 class="mt-3">Dirección</h6>
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Calle <span class="text-danger">*</span></label><input type="text" class="form-control" name="calle" value="{{ old('calle', $cliente->calle) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Número <span class="text-danger">*</span></label><input type="text" class="form-control" name="numero" value="{{ old('numero', $cliente->numero) }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Colonia <span class="text-danger">*</span></label><input type="text" class="form-control" name="colonia" value="{{ old('colonia', $cliente->colonia) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Código Postal <span class="text-danger">*</span></label><input type="text" class="form-control" name="codigo_postal" value="{{ old('codigo_postal', $cliente->codigo_postal) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Fecha Comprobante Domicilio <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_comprobante_domicilio" value="{{ old('fecha_comprobante_domicilio', $cliente->fecha_comprobante_domicilio) }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Municipio <span class="text-danger">*</span></label><input type="text" class="form-control" name="municipio" value="{{ old('municipio', $cliente->municipio) }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Estado <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado" value="{{ old('estado', $cliente->estado) }}" required></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <strong>Sección 2: Datos Laborales</strong>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Nombre del Negocio <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre_negocio" value="{{ old('nombre_negocio', $cliente->nombre_negocio) }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Giro del Negocio <span class="text-danger">*</span></label><input type="text" class="form-control" name="giro_negocio" value="{{ old('giro_negocio', $cliente->giro_negocio) }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Destino del Crédito <span class="text-danger">*</span></label><input type="text" class="form-control" name="destino_credito" value="{{ old('destino_credito', $cliente->destino_credito) }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Antigüedad del Negocio (años) <span class="text-danger">*</span></label><input type="number" class="form-control" name="antiguedad_negocio" value="{{ old('antiguedad_negocio', $cliente->antiguedad_negocio) }}" min="0" required></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>Sección 3: Referencias (se requieren 2)</strong>
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    @for ($i = 0; $i < 2; $i++)
                                        <h6 class="mt-3">Referencia {{ $i + 1 }}</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><label class="form-label">Nombre Completo <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][nombre_referencia]" value="{{ old('referencias.'.$i.'.nombre_referencia', $cliente->referencias[$i]->nombre_referencia ?? '') }}" required></div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Parentesco <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][parentesco]" value="{{ old('referencias.'.$i.'.parentesco', $cliente->referencias[$i]->parentesco ?? '') }}" required></div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Teléfono <span class="text-danger">*</span></label><input type="tel" class="form-control" name="referencias[{{ $i }}][telefono]" value="{{ old('referencias.'.$i.'.telefono', $cliente->referencias[$i]->telefono ?? '') }}" required></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <strong>Sección 4: Asignación</strong>
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label>
                                            <select class="form-select" name="id_sucursal" required>
                                                @foreach ($sucursales as $sucursal)
                                                    <option value="{{ $sucursal->id_sucursal }}" {{ old('id_sucursal', $cliente->id_sucursal) == $sucursal->id_sucursal ? 'selected' : '' }}>
                                                        {{ $sucursal->nombre_sucursal }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
```