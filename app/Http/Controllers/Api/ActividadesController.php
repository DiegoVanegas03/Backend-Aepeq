<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InscripcionResumenes;
use App\Models\InscripcionFotografia;
use App\Models\InscripcionTrajeTipico;
use App\Models\ColaboradorResumenes;
use App\Http\Requests\ResumenesRequest;
use App\Http\Controllers\Functions;
use App\Http\Requests\FotografiaRequest;

class ActividadesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(
            'getActiveSections',
            'registerResumenes',
            'getInfoResumen',
            'registerFotografia',
            'getInfoFotografia'
        );
        //$this->middleware('checkRole:7')->only('getTotalInfo');
    }

    public function getInfoFotografia(Request $request){
        $user = $request->user();
        $id_inscripcion = $request['id'];
        $info = InscripcionFotografia::where('user_id', $user->id)
        ->where('id', $id_inscripcion)->select('documento','descripcion','lugar_y_fecha', 'id')
        ->first();
        try{
            $rutaCompleta ='actividades/fotografia/'. $info->documento;
            $info['url'] = Functions::searchLinksS3($rutaCompleta);
            return response()->json(['info'=>$info], 200);
        }catch(\Exception $e){
            return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e], 500);
        }
    }

    public function registerFotografia(FotografiaRequest $request){
        $data = $request->validated();
        $user = $request->user();
        $count = InscripcionFotografia::where('user_id', $user->id)->count();
        if ($count < 3) {
            $intento = $count + 1;
            $inscripcion_fotografia = InscripcionFotografia::create([
                'user_id' => $user->id,
                'nombre_fotografia' => $data['nombre_fotografia'],
                'lugar_y_fecha' => $data['lugar_y_fecha'],
                'descripcion'=> $data['descripcion'],
            ]);
            try{
                $file = $request->file('fotografia');
                $carpeta = 'actividades/fotografia';
                switch($intento){
                    case 1:
                        $nombre ='primer_intento_' . 'fotografia_' . $user->id ;
                        break;
                    case 2:
                        $nombre ='segundo_intento_' . 'fotografia_' . $user->id ;
                        break;
                    case 3:
                        $nombre ='tercer_intento_' . 'fotografia_' . $user->id ;
                        break;
                    default:
                        break;
                };
                $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                $inscripcion_fotografia->documento = $nombre_doc;
                $inscripcion_fotografia->save();
                return response()->json(['message'=>'exito'], 200);
            }catch(\Exception $e){
                $inscripcion_fotografia->delete();
                return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e], 500);
            }   
        }else{
            return response()->json(['error' => 'error', 'message' => 'Limite de registro alcanzado'], 500);
        }
    }

    public function registerResumenes(ResumenesRequest $request){
        $data = $request->validated();
        $user = $request->user();
        $inscripcion_resumen = InscripcionResumenes::create([
            'user_id' => $user->id,
            'nombre_investigador' => $data['nombres'],
            'apellidos_investigador' => $data['apellidos'],
        ]);
        try{
            $file = $request->file('resumen');
            $nombre = 'resumen_' . $user->id ;
            $carpeta = 'actividades/resumenes';
            
            $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
            $inscripcion_resumen->nombre_archivo = $nombre_doc;
            $inscripcion_resumen->save();
            if(isset($data['colaboradores'])){
                $colaboradores = json_decode($data['colaboradores'], true);;
                foreach ($colaboradores as $colaborador) {
                    ColaboradorResumenes::create([
                        'nombres'=>$colaborador['nombres'],
                        'apellidos'=>$colaborador['apellidos'],
                        'inscripcion_id'=>$inscripcion_resumen->id
                    ]);
                }
            }
            return response()->json(['message'=>'exito'], 200);
        }catch (\Exception $e) {
            $inscripcion_resumen->delete();
            return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e], 500);
        }
    }

    public function getActiveSections(Request $request){
        $user = $request->user();
        $activeSections = array(
            'academicos'=> true,
            'fotografia' => true,
            'traje_tipico' => true
        );
        $traje_tipico = null;

        if(InscripcionResumenes::where('user_id',$user->id)->exists()){
            $activeSections['academicos'] = false;
        }

        if (InscripcionFotografia::where('user_id', $user->id)->count() > 2) {
            $activeSections['fotografia'] = false;
        }

        if(InscripcionTrajeTipico::where('primer_participante', $user->id)
        ->orWhere('segundo_participante', $user->id)->exists()){
            $activeSections['traje_tipico'] = false;
        }

        $resumen = InscripcionResumenes::where('user_id', $user->id)
        ->select('nombre_archivo')->first();

        $fotografia = InscripcionFotografia::where('user_id', $user->id)
        ->select('id','nombre_fotografia')->get();

        return response()->json(['activeSections'=>$activeSections, 'resumen'=>$resumen, 'fotografia'=>$fotografia, 'traje_tipico'=>$traje_tipico], 200);
    }

    public function getInfoResumen(Request $request){
        $user = $request->user();
        $resumen = InscripcionResumenes::where('user_id', $user->id)->first();
        try{
            $rutaCompleta ='actividades/resumenes/'. $resumen->nombre_archivo;
            $resumen['url'] = Functions::searchLinksS3($rutaCompleta);
            $colaboradores = ColaboradorResumenes::where('inscripcion_id',$resumen->id)
                            ->select('nombres', 'apellidos')->get();
            return response()->json(['info'=>$resumen, 'colaboradores'=>$colaboradores], 200);
        }catch(\Exception $e){
            return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e], 500);
        }
    }
}
