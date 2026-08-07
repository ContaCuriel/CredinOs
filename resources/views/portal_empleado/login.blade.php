<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Colaborador | Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            background-color: white;
        }

        /* Estilos para el Carrusel de Logos */
        .logo-carousel-container {
            width: 100%;
            max-width: 320px;
            margin: 0 auto 1rem auto;
            overflow: hidden;
            position: relative;
            height: 75px;
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
            max-height: 65px;
            width: auto;
            object-fit: contain;
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
        <div class="card login-card">
            <div class="p-4 pt-5 text-center">
                {{-- Carrusel de Logos --}}
                @isset($logos)
                    @if($logos->count() > 0)
                        <div class="logo-carousel-container">
                            <div class="logo-carousel-track">
                                @foreach($logos as $logo)
                                    <div class="logo-slide">
                                        <img src="{{ asset('storage/' . $logo) }}" alt="Logo Empresa">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endisset

                <h3 class="fw-bold text-dark mb-1">Mi Portal</h3>
                <p class="text-muted small mb-0">Acceso a comprobantes y recibos</p>
            </div>
            
            <div class="card-body p-4 p-md-4 pt-0">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show small rounded-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show small rounded-3 mb-4" role="alert">
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

                    <div class="mb-4">
                        <label for="id_empleado" class="form-label fw-bold text-secondary small">Número de Usuario / Empleado</label>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-primary">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input type="number" class="form-control form-control-custom border-start-0" id="id_empleado" name="id_empleado" required placeholder="Ej: 14" autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-custom shadow mt-2">
                        Ingresar <i class="bi bi-arrow-right ms-1"></i>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const track = document.querySelector('.logo-carousel-track');
        if (track && track.children.length > 1) {
            const slides = Array.from(track.children);
            let currentIndex = 0;

            function moveToNextSlide() {
                const slideWidth = slides[0].getBoundingClientRect().width;
                currentIndex = (currentIndex + 1) % slides.length;
                track.style.transform = 'translateX(-' + (slideWidth * currentIndex) + 'px)';
            }
            setInterval(moveToNextSlide, 4000);
        }
    });
    </script>
</body>
</html>