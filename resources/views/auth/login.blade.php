<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - {{ config('app.name', 'CredinOs') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 400px; width: 100%; padding: 2rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15); border-radius: .5rem; background-color: white; }
        .login-card h2 { margin-bottom: 1.5rem; text-align: center; }
        .form-group { text-align: left; }
        .invalid-feedback { text-align: left; display: block !important; }

        /* Estilos para el Carrusel de Logos */
        .logo-carousel-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 1.5rem auto; /* Margen inferior para separarlo del formulario */
            overflow: hidden;
            position: relative;
            height: 70px;
        }
        .logo-carousel-track {
            display: flex;
            align-items: center;
            transition: transform 0.5s ease-in-out;
            height: 100%;
        }
        .logo-slide {
            flex: 0 0 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .logo-slide img {
            max-height: 55px;
            width: auto;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">

            {{-- Carrusel de Logos --}}
            @isset($logos)
                @if($logos->count() > 0)
                    <div class="logo-carousel-container">
                        <div class="logo-carousel-track">
                            @foreach($logos as $logo)
                                <div class="logo-slide">
                                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo del Patrón">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endisset
            
            <h2>Iniciar Sesión</h2>
            
            {{-- Session Status --}}
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3 form-group">
                    <label for="email" class="form-label">Usuario (Email)</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Ingresa tu email">
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3 form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Ingresa tu contraseña">
                    @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3 form-check" style="text-align: left;">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">Entrar</button>

                <div class="text-center mt-3">
                    @if (Route::has('password.request'))
                        <a class="text-muted small" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                {{-- El enlace de registro ha sido eliminado --}}

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Script para el Carrusel --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const track = document.querySelector('.logo-carousel-track');
        if (track && track.children.length > 1) { // Solo activar si hay más de 1 logo
            const slides = Array.from(track.children);
            const slideWidth = slides[0].getBoundingClientRect().width;
            let currentIndex = 0;

            function moveToNextSlide() {
                currentIndex = (currentIndex + 1) % slides.length; // Avanza y vuelve al inicio
                track.style.transform = 'translateX(-' + (slideWidth * currentIndex) + 'px)';
            }
            setInterval(moveToNextSlide, 4000); // Mover cada 4 segundos
        }
    });
    </script>
</body>
</html>