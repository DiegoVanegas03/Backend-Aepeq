<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Congresista</title>
    <style>
        body ,html{
            font-family: Arial, sans-serif;
            background-color: #ecf0f1;
            max-height: 100%;
            max-width: 100%;
            margin: 0;
            padding-top:30%;
            padding-left: 20%;
        }

        .card {
            background-color: #2E61A9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            width: 450px;
        }

        h2 {
            color: #ffffff;
            letter-spacing: 4px;
        }

        img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            padding: 20px;
            background-color: #ffffff;
        }

        .redondeado {
            text-align: left;
            margin-top: 15px;
            background-color: #ffffff;
            padding-top: 6px;
            padding-bottom: 6px;
            padding-left: 10px;
            padding-right: 10px;
            border-radius: 5px;
            text-transform: uppercase;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
        }

        strong {
            color: #2E61A9;
        }
        
    </style>
</head>
<body>
    <div class="card">
        <h2>ACCESO AL CONGRESO</h2>

        <img src="{{ $congresista['qr_image'] }}" alt="QR Code">

        <div class="redondeado">
            <strong>NOMBRE:</strong> <em>{{ $congresista['nombre'] .' '. $congresista['apellido'] }}</em>
        </div>

        <div class="redondeado">
            <strong>NÚMERO DEL CONGRESISTA:</strong> {{ $congresista['numero'] }}
        </div>
    </div>

</body>
</html>
