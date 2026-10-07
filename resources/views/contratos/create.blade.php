<x-app-layout>
    <style>
        /* Fondo Animado iOS Mesh Gradient Sutil */
        .bg-system-islands {
            background: linear-gradient(-45deg, #f0f4fd, #f4f0fa, #f0faf5, #f5f8fc);
            background-size: 400% 400%;
            animation: subtleMesh 20s ease infinite;
            min-height: calc(100vh - 60px);
            padding-bottom: 3rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        @keyframes subtleMesh {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Tarjeta Estilo Isla */
        .ios-island {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }

        /* Controles de Formulario */
        .form-control-ios, .form-select-ios {
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: rgba(255,255,255,0.8);
            padding: 0.6rem 1rem;
            color: #1d1d1f;
            transition: all 0.3s ease;
        }
        .form-control-ios:focus, .form-select-ios:focus {
            background-color: #ffffff;
            border-color: #0071e3;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.15);
            outline: none;
        }

        .form-label-ios {
            font-weight: 600;
            font-size: 0.85rem;
            color: #86868b;
            margin-bottom: 0.3rem;
            margin-left: 0.2rem;
        }

        /* Botones estilo iOS */
        .btn-ios-primary {
            background: linear-gradient(135deg, #0071e3, #005bb5);
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 113, 227, 0.2);
            transition: all 0.2s;
        }
        .btn-ios-primary:hover {
            background: linear-gradient(135deg, #0077ED, #0062c4);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(0, 113, 227, 0.35);
            color: white;
        }

        .btn-ios-secondary {
            background-color: rgba(255,255,255,0.6);
            color: #6e6e73;
            border-radius: 2rem;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .btn-ios-secondary:hover {
            background-color: rgba(0,0,0,0.03);
            color: #1d1d1f;
        }
    </style>

    <div class="container-fluid pt-5 px-4 bg-system-islands">
        
        <div class="ios-island">
            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                <i class="bi bi-file-earmark-plus-fill text-primary fs-3 me-3"></i>
                <h4 class="mb-0 fw-bold" style="color: #1d1d1f; letter-spacing: -0.5px;">Registrar Nuevo Contrato</h4>
            </div>

            {{-- ========================================== --}}
            {{-- BLOQUE DE ALERTAS Y ERRORES (IMSS y Validaciones) --}}
            {{-- ========================================== --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 py-3 border-0 d-flex align-items-center shadow-sm" role="alert" style="background-color: rgba(252, 232, 230, 0.9); color: #dc3545; border-radius: 1rem;">
                    <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                    <div>
                        <strong>¡Atención! Acción denegada por el sistema:</strong><br>
                        <span style="font-size: 0.95rem;">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4 py-3 border-0 shadow-sm" role="alert" style="background-color: rgba(252, 232, 230, 0.9); color: #dc3545; border-radius: 1rem;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-shield-x me-2 fs-5"></i>
                        <strong>Por favor corrige los siguientes errores:</strong>
                    </div>
                    <ul class="mb-0 ps-4" style="font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            {{-- ========================================== --}}

            <form action="{{ route('contratos.store') }}" method="POST">
                @csrf

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="id_empleado" class="form-label-ios">Empleado <span class="text-danger">*</span></label>
                        <select class="form-select form-select-ios @error('id_empleado') is-invalid @enderror" id="id_empleado" name="id_empleado" required>
                            <option value="">Seleccione un empleado...</option>
                            @foreach ($empleados as $empleado)
                                <option value="{{ $empleado->id_empleado }}" {{ $prefill_empleado_id == $empleado->id_empleado ? 'selected' : '' }}>
                                    {{ $empleado->nombre_completo }} ({{ $empleado->rfc }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="id_patron" class="form-label-ios">Patrón (Empresa/Contratante) <span class="text-danger">*</span></label>
                        <select class="form-select form-select-ios @error('id_patron') is-invalid @enderror" id="id_patron" name="id_patron" required>
                            <option value="">Seleccione un Patrón...</option>
                            @if(isset($patrones))
                                @foreach ($patrones as $patron)
                                    <option value="{{ $patron->id_patron }}" 
                                            data-tipo_persona="{{ $patron->tipo_persona }}" 
                                            {{ (old('id_patron', $prefill_patron_id ?? null) == $patron->id_patron) ? 'selected' : '' }}>
                                        {{ $patron->razon_social }} ({{ $patron->rfc }}) - {{ ucfirst($patron->tipo_persona) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('id_patron') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="tipo_contrato" class="form-label-ios">Tipo de Contrato <span class="text-danger">*</span></label>
                        <select class="form-select form-select-ios @error('tipo_contrato') is-invalid @enderror" id="tipo_contrato" name="tipo_contrato" required>
                            <option value="">Seleccione un tipo...</option>
                            @foreach ($tipos_contrato as $valor => $texto)
                                <option value="{{ $valor }}" {{ $prefill_tipo_contrato == $valor ? 'selected' : '' }}>
                                    {{ $texto }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_contrato') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_inicio" class="form-label-ios">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-ios @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                        @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="fecha_fin" class="form-label-ios">Fecha de Fin <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-ios @error('fecha_fin') is-invalid @enderror" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                        @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <hr style="opacity: 0.1;">
                
                <div class="text-end mt-4">
                    <a href="{{ route('contratos.index') }}" class="btn-ios-secondary text-decoration-none me-2">Cancelar</a>
                    <button type="submit" class="btn-ios-primary"><i class="bi bi-floppy-fill me-1"></i> Guardar Contrato</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>