<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Comite;
use App\Http\Controllers\Controller;

class ComitesController extends Controller{
    public function __construct(){
        $this->middleware('auth:sanctum')->only('updateComites');
        $this->middleware('checkRole:7')->only('updateComites');
    }

    public function updateComites(Request $request){
        $id = $request['id'];
        $campo = $request['campo'];
        $valor = $request['valor'];
        $comite = Comite::find($id);
        if(!$comite){
            return response()->json(['message' => 'No se encontro el programa'], 404);
        }
        $comite[$campo] = $valor;
        $comite->save();
        return response()->json(['message' => 'Programa actualizado correctamente'], 200);
    }

    public function getTotalInfo()
    {
        return Comite::all();
    }
}
