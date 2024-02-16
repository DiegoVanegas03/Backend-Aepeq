<?php

namespace App\Exports;

use App\Models\InscripcionTrajeTipico;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Http\Controllers\Functions;

class TrajeTipicoExport implements FromCollection, WithHeadings{
    public function headings(): array{
        return [
            'index',
            'numero_primer_participante',
            'numero_segundo_participante',
            'nombre_primer_participante',
            'nombre_segundo_participante',
            'estado_representante',
            'link_reseña',
            'link_mp3'
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(){
        $collection = InscripcionTrajeTipico::select(
            'id',
            'primer_participante',
            'segundo_participante',
            'estado_representante',
            'nombre_doc',
            'nombre_pista',
        )->get(); 
        $carpeta_reseña = 'actividades/traje_tipico/reseña/';
        $carpeta_mp3 = 'actividades/traje_tipico/mp3/';
        foreach($collection as $item){
            $item['nombre_primer_participante'] = $item->primerParticipante->nombres.' '.$item->primerParticipante->apellidos;
            unset($item->primerParticipante);
            $item['nombre_segundo_participante'] = $item->segundoParticipante->nombres.' '.$item->segundoParticipante->apellidos;
            unset($item->segundoParticipante);
            $pivEstado = $item->estado->estado;
            unset($item->estado_representante);
            $item['estado'] = $pivEstado;
            $item['url_doc'] = Functions::searchLinksS3($carpeta_reseña.$item->nombre_doc);
            $item['url_mp3'] = Functions::searchLinksS3($carpeta_mp3.$item->nombre_pista);
            unset($item->nombre_doc);
            unset($item->nombre_pista);
        }
        return $collection ;
    }
}
