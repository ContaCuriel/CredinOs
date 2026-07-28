<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo_documento }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.6; margin: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header img { max-width: 200px; max-height: 80px; }
        .content { text-align: justify; }
        .content h1, .content h2, .content h3 { text-align: center; margin-bottom: 15px; }
        .content p { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if($logo_base64)
            <img src="{{ $logo_base64 }}" alt="Logo">
        @else
            <h2>{{ $patron->razon_social }}</h2>
        @endif
    </div>

    <div class="content">
        {!! $html_content !!}
    </div>
</body>
</html>