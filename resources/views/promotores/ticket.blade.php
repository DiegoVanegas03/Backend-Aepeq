<!DOCTYPE html>
<html>
<head>
    <title>Claves para {{ $nombre }}</title>
    <style>
        .center {
            text-align: center;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1 class="center">Ticket de registro</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo de Inscripción</th>
                <th>Token de registro</th>
                <th>URL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tokens as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->nombre }}</td>
                    <td>{{ $item->tipo_inscripcion }}</td>
                    <td>{{ $item->token_de_registro }}</td>
                    <td>{{ $item->url }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>