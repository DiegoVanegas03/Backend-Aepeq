<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Programa;
use App\Models\User;
use App\Notifications\CambioEnPrograma;

class ProgramaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only('updatePonencias');
        $this->middleware('checkRole:7')->only('updatePonencias');
    }

    public function updatePonencias(Request $request){
        $data = $request->all();
        $programa = Programa::find($data['id']);
        if(!$programa){
            return response()->json(['message' => 'No se encontró el registro'], 404);
        }
        $programa[$data['campo']] = $data['valor'];
        $programa->save();
        $users = User::all();
        foreach($users as $user){
            $user->notify(new CambioEnPrograma($data['campo'], $data['valor']));
        }
        return response()->json(['message' => 'Se actualizó el registro'], 200);
    }

    public function getPonencias(){
        $dia14 = Programa::where('dia',14)->get()->map(function ($pr) {
            $keysToFilter = array_flip(['created_at','updated_at']);
            $pr = array_diff_key($pr->toArray(), $keysToFilter);
            return $pr;
        })->sortBy('horario')->values();
        $dia15 = Programa::where('dia',15)->get()->map(function ($pr) {
            $keysToFilter = array_flip(['created_at','updated_at']);
            $pr = array_diff_key($pr->toArray(), $keysToFilter);
            return $pr;
        })->sortBy('horario')->values();
        $dia16 = Programa::where('dia',16)->get()->map(function ($pr) {
            $keysToFilter = array_flip(['created_at','updated_at']);
            $pr = array_diff_key($pr->toArray(), $keysToFilter);
            return $pr;
        })->sortBy('horario')->values();
        return response()->json(['dia14'=>$dia14, 'dia15'=>$dia15, 'dia16'=>$dia16], 200);
    }
}
