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
use App\Notifications\CambioDeAula;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TalleresExport;

class TalleresController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only(
            'getIds',
            'registro_taller',
            'desinscripcion_taller',
            'getInfoTaller',
            'updateInfoTaller',
            'addTaller',
            'deleteTaller',
            'changeAula',
            'descargar_listas',
        );
        $this->middleware('checkRole:7')->only(
            'getInfoTaller',
            'updateInfoTaller',
            'addTaller',
            'deleteTaller',
            'changeAula',
            'descargar_listas'
        );
    }

    public function descargar_listas(Request $request){
        $request->validate([
            'dia'=>'required|integer',
        ]);
        $dia = $request->dia;
        return Excel::download(new TalleresExport($dia), 'archivo.xlsx');
    }

    public function changeAula(Request $request){
        $request->validate([
            'idAula1'=>'required|string',
            'idAula2'=>'required|string',
        ]);
        $id_aula1 = $request['idAula1'];
        $id_aula2 = $request['idAula2'];
        $taller_1 = Taller::find($id_aula1);
        $taller_2 = Taller::find($id_aula2);
        $pivot = $taller_1->aula;
        $taller_1->aula = $taller_2->aula;
        $taller_2->aula = $pivot;
        $taller_1->save();
        $taller_2->save();
        $inscripciones = InscripcionTaller::where('taller_id', $taller_1->id)->get();
        foreach($inscripciones as $inscripcion){
            $user = $inscripcion->user;
            $user->notify(new CambioDeAula($taller_1->aula,$taller_1->nombre_taller));
        }
        $inscripciones = InscripcionTaller::where('taller_id', $taller_2->id)->get();
        foreach($inscripciones as $inscripcion){
            $user = $inscripcion->user;
            $user->notify(new CambioDeAula($taller_2->aula,$taller_2->nombre_taller));
        }
        return response()->json(['message' => 'Actualización exitosa'], 200);
    }

    public function deleteTaller(Request $request){
        $request->validate([
            'id'=>'required|integer',
        ]);
        $id = $request['id'];
        $taller = Taller::where('id',$id)->first();
        $inscripciones = InscripcionTaller::where('taller_id', $taller->id)->get();
        foreach($inscripciones as $inscripcion){
            $user = $inscripcion->user;
            $inscripcion->delete();
            $user->notify(new DesinscripcionForzada($taller->nombre_taller));
        }
        $taller->delete();
        event(new UpdateTalleres("Se elimino el taller ". $taller->nombre_taller));
        return response()->json(['message' => 'Eliminación exitosa'], 200);
    }

    public function addTaller(Request $request){
        $request->validate([
            'nombre_taller'=>'required|string',
            'capacidad_maxima'=>'required|integer',
            'aula'=>'required|string',
            'descripcion'=>'nullable|string',
            'ponente'=>'required|string',
            'dia'=>'required|integer',
        ]);
        $aula = strtoupper($request['aula']);
        if(preg_split('/\s+/', $aula)[0]!== 'AULA'){
            return response()->json(['error' =>'error', 'message'=> 'El aula debe de tener el formato AULA # (si el numero es de un digito agregar 0 antes:`01`)'], 500);
        }

        $aulas_existentes = Taller::where('dia',$request['dia'])->select('aula')->get();
        foreach($aulas_existentes as $aula_existente){
            if($aula_existente->aula === $aula){
                return response()->json(['error' =>'error', 'message'=> 'El aula ya esta ocupada,si son cambios pequeños modifiquela si es todo eliminela y suba la actualizada.'], 500);
            }
        }
        $taller = Taller::create([
            'nombre_taller'=>$request['nombre_taller'],
            'capacidad_maxima'=>$request['capacidad_maxima'],
            'aula'=>$aula,
            'descripcion'=>$request['descripcion'],
            'ponente'=>$request['ponente'],
            'dia'=>$request['dia'],
        ]);
        event(new UpdateTalleres("Se añadio el taller de aula ". $taller->aula));
        return response()->json(['message' => 'Registro exitoso'], 200);
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
                    $user->notify(new DesinscripcionForzada($taller->nombre_taller));
                    $inscripcion->delete();
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
        $totalInscritos = InscripcionTaller::all()->count();
        $talleres = Taller::where('dia', $dia)->orderByRaw('LENGTH(aula), aula')->get();
        $count = 0;
        foreach($talleres as $taller){
            $taller->inscritos = InscripcionTaller::where('taller_id',$taller->id)->count();
            $count += $taller->inscritos;
        }
        return response()->json(['talleres'=>$talleres,'inscritos'=>$count, 'total'=>$totalInscritos], 200);
    }

    public function getInfoTalleres(){
        $talleres = Taller::all();
        $inscritos=[];
        foreach($talleres as $taller){
            $inscritos[$taller->id] = InscripcionTaller::where('taller_id',$taller->id)->count();
        }
        
        $T13 = Taller::where('dia', 13)->orderByRaw('LENGTH(aula), aula')->get();
        $T14 = Taller::where('dia', 14)->orderByRaw('LENGTH(aula), aula')->get();
        $T15 = Taller::where('dia', 15)->orderByRaw('LENGTH(aula), aula')->get();

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
        $inscrito = InscripcionTaller::where('user_id', $user->id)->get();
        foreach($inscrito as $inscrito){
            if($inscrito->taller->dia === $taller->dia){
                $details = [
                    'id_inscrito'=>$inscrito->taller->id,
                    'id_nuevo'=>$taller->id,
                    'nombre_taller'=>$inscrito->taller->nombre_taller,
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
