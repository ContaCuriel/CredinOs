<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Empleado | Recibos de Nómina</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card { 
            width: 100%;
            max-width: 420px; 
            border-radius: 1.25rem; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
            border: none; 
            overflow: hidden;
        }
        .login-header { 
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); 
            color: white; 
            padding: 2.5rem 1.5rem; 
            text-align: center; 
        }
        .form-control-custom { 
            border-radius: 0.5rem; 
            padding: 0.75rem 1rem; 
            border: 1px solid #dee2e6;
        }
        .form-control-custom:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
        .btn-custom { 
            border-radius: 0.5rem; 
            padding: 0.85rem; 
            font-weight: 600; 
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="card login-card bg-white">
            <div class="login-header">
                <i class="bi bi-wallet2" style="font-size: 3rem;"></i>
                <h3 class="mt-3 mb-1 fw-bold">Mi Nómina</h3>
                <p class="mb-0 text-white-50 small">Acceso al portal del trabajador</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show small rounded-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show small rounded-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('portal.acceder') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="rfc" class="form-label fw-bold text-secondary small">Registro Federal de Contribuyentes (RFC)</label>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-primary">
                                <i class="bi bi-person-vcard"></i>
                            </span>
                            <input type="text" class="form-control form-control-custom border-start-0 text-uppercase" id="rfc" name="rfc" required placeholder="Ingresa tu RFC" maxlength="13" autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="id_empleado" class="form-label fw-bold text-secondary small">Número de Empleado</label>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-primary">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input type="number" class="form-control form-control-custom border-start-0" id="id_empleado" name="id_empleado" required placeholder="Ej: 14" autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-custom shadow">
                        Ingresar a mis recibos <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
            
            <div class="card-footer text-center py-3 bg-light border-0">
                <small class="text-muted">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i> Información encriptada y confidencial
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>