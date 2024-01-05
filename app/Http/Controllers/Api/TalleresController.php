<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Taller;
use App\Models\InscripcionTaller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\ActualizacionTaller;
use App\Notifications\DesinscripcionForzada;
use App\Events\UpdateTalleres;

class TalleresController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only(
            'getIds',
            'registro_taller',
            'desinscripcion_taller',
            'getInfoTaller',
            'updateInfoTaller'
        );
        $this->middleware('checkRole:7')->only(
            'getInfoTaller',
            'updateInfoTaller'
        );
    }

    public function updateInfoTaller(Request $request){
        $request->validate([
            'id'=>'required|integer',
            'campo' => 'required|string',
            'valor'=>'required|string',
        ]);
        $id = $request['id'];
        $campo = $request['campo'];
        $valor = $request['valor'];

        $taller = Taller::where('id',$id)->first();
        $oldValue = $taller[$campo];
        $taller[$campo] = $valor;
        
        $labels = [
            'nombre_taller'=>'Nombre del taller',
            'capacidad_maxima'=>'Capacidad máxima',
            'aula'=>'Aula',
            'descripcion'=>'Descripción',
            'ponente'=>'Ponente',
        ];
        if($campo === 'capacidad_maxima'){
            $inscritos_count = InscripcionTaller::where('taller_id',$id)->count();
            if($inscritos_count > $valor){
                $inscripciones = InscripcionTaller::where('taller_id', $taller->id)->get();
                $inscripcionesExcedentes = $inscripciones->slice($valor);
                foreach($inscripcionesExcedentes as $inscripcion){
                    $user = $inscripcion->user;
                    $inscripcion->delete();
                    $user->notify(new DesinscripcionForzada($oldValue,$valor, $labels[$campo]));
                }
            }
        }else{
            $inscripciones = InscripcionTaller::where('taller_id', $taller->id)->get();
            foreach($inscripciones as $inscripcion){
                $user = $inscripcion->user;
                $user->notify(new ActualizacionTaller($oldValue,$valor, $labels[$campo]));
            }
        }
        $taller->save();
        event(new UpdateTalleres("Se actualizo el taller ". $taller->nombre_taller));
        return response()->json(['message' => 'Actualización exitosa'], 200);
    }

    public function getInfoTaller(Request $request){
        $dia = $request['dia'];
        $talleres = Taller::where('dia', $dia)->get();
        foreach($talleres as $taller){
            $taller->inscritos = InscripcionTaller::where('taller_id',$taller->id)->count();
        }
        return response()->json(['talleres'=>$talleres], 200);
    }

    public function getInfoTalleres(){
        $talleres = Taller::all();
        $inscritos=[];
        foreach($talleres as $taller){
            $inscritos[$taller->id] = InscripcionTaller::where('taller_id',$taller->id)->count();
        }
        
        $T13 = Taller::where('dia', 13)->get();
        $T14 = Taller::where('dia', 14)->get();
        $T15 = Taller::where('dia', 15)->get();

        $talleres = ['Dia13'=>$T13,'Dia14'=> $T14, 'Dia15'=>$T15];
        
        return response()->json(['talleres'=>$talleres, 'inscritos'=>$inscritos], 200);
    }
    
    public function desinscripcion_taller(Request $request){
        $user = $request->user();
        $id = $request['id'];
        $inscripcion = InscripcionTaller::where('user_id',$user->id)->where('taller_id', $id)->first();
        $taller = $inscripcion->taller;
        $inscripcion->delete();
        event(new UpdateTalleres("Alguien se ha desinscrito al taller ". $taller->nombre_taller));
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
            event(new UpdateTalleres("Alguien se ha inscrito al taller ". $taller->nombre_taller));
            return response()->json(['message' => 'Registro exitoso'], 200);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['error' =>'error', 'message'=> $e->getMessage()], 500);
        }
    }
}
