<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña - {{ config('app.name', 'CredinOs') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Usamos exactamente los mismos estilos de tu página de login --}}
    <style>
        body { background-color: #f8f9fa; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 400px; width: 100%; padding: 2rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15); border-radius: .5rem; background-color: white; }
        .login-card h2 { margin-bottom: 1rem; text-align: center; }
        .form-group { text-align: left; }
        .invalid-feedback { text-align: left; display: block !important; }
        .text-muted-small { font-size: 0.875em; color: #6c757d; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">

            <h2>¿Olvidaste tu contraseña?</h2>
            
            <div class="mb-4 text-muted-small">
                No hay problema. Solo déjanos tu dirección de correo electrónico y te enviaremos un enlace para que puedas elegir una nueva.
            </div>

            <x-auth-session-status class="mb-4 alert alert-success" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3 form-group">
                    <label for="email" class="form-label">Usuario (Email)</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Ingresa tu email">
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary mt-3">
                        Enviar Enlace de Restablecimiento
                    </button>
                </div>

                <div class="text-center mt-4">
                    <a class="text-muted small" href="{{ route('login') }}">
                        Volver a Iniciar Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>