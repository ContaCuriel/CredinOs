    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Iniciar Sesión</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background-color: #f8f9fa; }
            .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .login-card { max-width: 400px; width: 100%; padding: 2rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15); border-radius: .5rem; }
            .login-card h2 { margin-bottom: 1.5rem; text-align: center; }
            .form-group { text-align: left; }
            .invalid-feedback { text-align: left; display: block !important; }
            
            /* --- ESTILOS PARA EL CARRUSEL DE LOGOS --- */
            .logo-carousel-container {
                height: 80px; /* Altura fija para evitar saltos en la página */
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .carousel-item img {
                max-height: 60px; /* Altura máxima para los logos */
                width: auto;      /* Ancho automático para mantener la proporción */
                max-width: 200px; /* Ancho máximo para logos muy anchos */
            }
            /* --- FIN DE ESTILOS --- */
        </style>
  </head>
  <body>
    <div class="login-container">
        <div class="login-card">
            
        <h2>Iniciar Sesión</h2>
        
        {{-- Session Status (si se envía alguno desde el controlador) --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf <div class="mb-3 form-group">
                <label for="email" class="form-label">Usuario (Email)</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Ingresa tu email">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3 form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Ingresa tu contraseña">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3 form-check" style="text-align: left;">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Recordarme
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3">Entrar</button>

            <div class="text-center mt-3">
                @if (Route::has('password.request'))
                    <a class="text-muted small" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

             <div class="text-center mt-2">
                <a class="text-muted small" href="{{ route('register') }}">
                    ¿No tienes cuenta? Regístrate
                </a>
            </div>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>