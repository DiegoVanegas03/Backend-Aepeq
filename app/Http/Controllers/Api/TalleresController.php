<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Taller;
use App\Models\InscripcionTaller;
use Illuminate\Http\Request;
use App\Http\Controllers\Functions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TalleresController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only(
            'infoDataUser',
            'registerPayment',
            'getIds',
            'registro_taller',
            'desinscripcion_taller',
        );

    }
    public function getInfoTalleres(){
        $talleres = Taller::all();
        $inscritos=[];
        foreach($talleres as $taller){
            $inscritos[$taller->id] = InscripcionTaller::where('taller_id',$taller->id)->count();
        }

        $T14 = Taller::where('dia', 14)->get();
        $T15 = Taller::where('dia', 15)->get();

        $talleres = ['Dia14'=> $T14, 'Dia15'=>$T15];
        
        return response()->json(['talleres'=>$talleres, 'inscritos'=>$inscritos], 200);
    }
    
    public function desinscripcion_taller(Request $request){
        $user = $request->user();
        $id = $request['id'];
        InscripcionTaller::where('user_id',$user->id)->where('taller_id', $id)->delete();
        return response()->json(['message' => 'Desinscripcion exitosa'], 200);
    }

    public function getIds(Request $request){
        $user = $request->user();
        $ids = array();
        $inscripciones = InscripcionTaller::where('user_id', $user->id)->get();
        foreach($inscripciones as $inscripcion){
            $taller = $inscripcion->taller;
            $ids[] = ['Dia'=>$taller->dia,"id"=>$taller->id];
        }
        return response()->json(['ids'=>$ids],200);
    }

    public function registro_taller(Request $request){
        $user = $request->user();
        $id = $request['id'];
        $taller = Taller::where('id',$id)->first();
        $inscritos_count = InscripcionTaller::where('taller_id',$id)->count();
        $inscrito = InscripcionTaller::where('user_id', $user->id)->first();

        if($inscrito){
            $taller_inscrito = $inscrito->taller;
            if($taller_inscrito->dia === $taller->dia){
                $details = [
                    'id_inscrito'=>$taller_inscrito->id,
                    'id_nuevo'=>$taller->id,
                    'nombre_taller'=>$taller_inscrito->nombre_taller,
                ];
                return response()->json([
                    'error' =>'error',
                    'message'=> 'Taller Inscrito',
                    'details'=>$details
                ], 500);    
            }
        }

        try{
            if($inscritos_count >= $taller->capacidad_maxima){
                throw new \Exception('El taller ha alcanzado su capacidad máxima.');
            }
            DB::beginTransaction();
            InscripcionTaller::create([
                'user_id'=>$user->id,
                'taller_id'=>$id
            ]);
            DB::commit();
            return response()->json(['message' => 'Registro exitoso'], 200);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['error' =>'error', 'message'=> $e->getMessage()], 500);
        }
    }
}
