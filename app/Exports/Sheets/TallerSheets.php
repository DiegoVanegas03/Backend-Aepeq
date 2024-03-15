<?php

namespace App\Exports\Sheets;


use App\Models\InscripcionTaller;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TallerSheets implements WithTitle, FromCollection, WithHeadings{
    
    private $id;
    private $aula;

    public function __construct(int $id, string $aula){
        $this->id = $id;
        $this->aula = $aula;
    }

    public function headings(): array
    {
        return [
            'numero_congresista',
            'nombre_completo',
            'correo'
        ];
    }

    public function collection(){
        $collection = InscripcionTaller::select('user_id')->with('user')->where('taller_id',$this->id)->get();
        foreach($collection as $row){
            $row['nombre'] = $row->user->nombres.' '.$row->user->apellidos;
            $row['email'] = $row->user->email;
            $row['user'] = null;
        }
        return $collection;
    }

    /**
     * @return string
     */
    public function title(): string{
        return  $this->aula;
    }

}
