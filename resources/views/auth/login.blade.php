<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - {{ config('app.name', 'CredinOs') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Fondo Animado Estilo iOS Mesh Gradient */
        body { 
            margin: 0;
            padding: 0;
            background: linear-gradient(-45deg, #a1c4fd, #c2e9fb, #e0c3fc, #8ec5fc);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-container { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 1rem;
        }

        /* Tarjeta Estilo Glassmorphism (Cristal) */
        .login-card { 
            max-width: 420px; 
            width: 100%; 
            padding: 2.5rem 2rem; 
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border-radius: 1.5rem; 
        }

        .login-card h2 { 
            margin-bottom: 1.5rem; 
            text-align: center; 
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.5px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #515154;
            margin-bottom: 0.3rem;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: #0071e3;
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1);
        }

        .btn-primary {
            background-color: #0071e3;
            border: none;
            border-radius: 2rem;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0077ED;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
        }

        .form-group { text-align: left; }
        .invalid-feedback { text-align: left; display: block !important; }

        /* Estilos para el Carrusel de Logos */
        .logo-carousel-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 2rem auto;
            overflow: hidden;
            position: relative;
            height: 70px;
        }
        .logo-carousel-track {
            display: flex;
            align-items: center;
            transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
            height: 100%;
        }
        .logo-slide {
            flex: 0 0 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .logo-slide img {
            max-height: 60px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }

        .footer-quote {
            text-align: center;
            margin-top: 2rem;
            color: #86868b;
        }
        .footer-quote p {
            margin-bottom: 0.2rem;
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
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tu@correo.com">
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-4 form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
                    @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-4 form-check d-flex align-items-center" style="text-align: left;">
                    <input class="form-check-input mt-0 me-2" type="checkbox" name="remember" id="remember" style="cursor: pointer;">
                    <label class="form-check-label text-secondary" for="remember" style="font-size: 0.9rem; cursor: pointer;">Recordarme en este equipo</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar al Sistema</button>

                <div class="text-center mt-4">
                    @if (Route::has('password.request'))
                        <a class="text-decoration-none" href="{{ route('password.request') }}" style="color: #0071e3; font-size: 0.9rem; font-weight: 500;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>
            </form>
            
            <div class="footer-quote">
                <p><em>"El futuro pertenece a quienes creen en la belleza de sus sueños."</em></p>
                <p style="font-size: 0.75rem; font-weight: 500; margin-top: 10px;">Creado por Carlos Curiel & Gemini</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Script para el Carrusel --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const track = document.querySelector('.logo-carousel-track');
        if (track && track.children.length > 1) { 
            const slides = Array.from(track.children);
            const slideWidth = slides[0].getBoundingClientRect().width;
            let currentIndex = 0;

            function moveToNextSlide() {
                currentIndex = (currentIndex + 1) % slides.length; 
                track.style.transform = 'translateX(-' + (slideWidth * currentIndex) + 'px)';
            }
            setInterval(moveToNextSlide, 4000);
        }
    });
    </script>
</body>
</html>