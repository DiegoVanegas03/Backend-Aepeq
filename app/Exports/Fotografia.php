<?php

namespace App\Exports;

use App\Models\InscripcionFotografia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Http\Controllers\Functions;

class Fotografia implements FromCollection, WithHeadings{

    public function headings(): array{
        return [
            'numero_congresista',
            'nombre_fotografia',
            'lugar_y_fecha',
            'descripcion',
            'nombre_completo',
            'link_archivo',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()    {
        $collection = InscripcionFotografia::select(
            'user_id',
            'nombre_fotografia',
            'lugar_y_fecha',
            'descripcion',
            'documento',
        )->get();
        $carpeta = 'actividades/fotografia/';
        foreach($collection as $item){
            $item['nombre_completo'] = $item->user->nombres.' '.$item->user->apellidos;
            $item['link_archivo'] = Functions::searchLinksS3($carpeta.$item->documento);
            unset($item['user']);
            unset($item['documento']);
        }
        return $collection;
    }
}
