<?php

namespace App\Exports;

use App\Models\Taller;
use App\Exports\Sheets\TallerSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TalleresExport implements WithMultipleSheets{

    use Exportable;


    private $dia;
    public function __construct(int $dia)
    {
        $this->dia = $dia;
    }

    /**
     * @return array
    */
    public function sheets(): array{

        $sheets = [];

        $talleres = Taller::where('dia', $this->dia)->orderByRaw('LENGTH(aula), aula')->get();

        foreach($talleres as $taller){
            $sheets[] = new TallerSheets($taller->id, $taller->aula);
        }

        return $sheets;
    }
}
