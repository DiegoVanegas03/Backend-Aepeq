<!DOCTYPE html>
<html>
<head>
    <title>Resumen de aceptación</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root{
            font-family:'Times New Roman', Times, serif;
        }
        body, html {
            max-height: 100%;
            max-width: 100%;
            margin: 0;
            padding-top:150px;
        }
        p{
            word-break: break-all;

        }
        header{
            width: 100%;
            text-align: right;
            font-weight: bold;
        }
        .lineas{
            padding-right: 80px;
        }
        
        .contenido{
            width: 100%;
            font-size: 15px;
            padding-left: 90px;
        }
        .bold{
            font-weight: bold;
        }
        .nombres{
            font-size: 13px;
            padding-left: 5px;
        }
        .articulo{
            text-transform: uppercase;
            
        }
        .max-80-width{
            max-width: 80%;
        }
        .max-70-width{
            max-width: 70%;
        }
        ul {
            list-style-type: none;
        }
        .min-h-200{
            min-height: 200px;
        }
    </style>  
</head>
<body >
    <header>
        <p class="lineas">No. Oficio:{{str_pad($inscripcion->id, 3, '0', STR_PAD_LEFT)}}/2024</p>
        <p class="lineas">San Luis Potosí, S.L.P {{$date}}</p>
    </header>
    <div class="contenido">
        <p class="bold">Estimado (s) autor (es):</p>
        <div>
            <p class="bold nombres">{{$inscripcion->apellidos_investigador.' '.$inscripcion->nombre_investigador }}.</p>
            @foreach($colaboradores as $colaborador)
                <span class="nombres">{{ $colaborador->apellidos . ' ' . $colaborador->nombres }}.</span>
            @endforeach
        </div>
        <p class="articulo max-80-width">
            <strong>Artículo:</strong>
            {{$titulo}}.
        </p>
        <p>Con número de congresista: #{{$inscripcion->user->id}}</p>
        <p class="max-80-width">
            Con agrado le informamos que el Comité científico de AEPEQ en el margen del congreso: 
            <em>VI internacional y XXII nacional de enfermería quirúrgica 2024</em>, ha recibido y aprobado su 
            resumen. Por favor verifique que la relación de su resumen concluya con la estructura de la 
            convocatoria, mande su extenso y <u>especifique la modalidad de su trabajo <strong>(cartel y/ o presentación 
            oral).</strong></u>
        </p>
        <p class="max-80-width">
            Le solicitamos de la manera más atenta que en toda su correspondencia con el <strong><em>comité de AEPEQ
            trabajos científicos</em></strong>, mencione siempre su <strong>número de folio</strong>.
        </p>
        <div class = "min-h-200">
            @if(isset($comentarios))
                <p class="max-80-width">
                    Se adjunto a esta carta los comentarios y recomendaciones realizadas por los revisores de su artículo. 
                    Le solicitamos realizar las correcciones pertinentes a su artículo con el formato requerido. Y también 
                    enviarnos la versión final de su artículo antes del 28 de febrero 2024. 
                </p>
                <ul>
                    @foreach($comentarios as $numero=>$comentario)
                    <li class="max-70-width">
                        <strong>{{$numero + 1}}.</strong> {{$comentario}}
                    </li>
                    @endforeach
                </ul>
            @else
                <p class="max-80-width">
                    Favor de enviarnos la versión final de su artículo antes del 28 de febrero 2024. 
                </p>
            @endif
        </div>
        <p class="max-80-width">Sin otra particular reciba un grato saludo.</p>
    </div>
    <footer></footer>
</body>
</html>