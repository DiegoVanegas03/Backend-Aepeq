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
use App\Models\EstadosMexico;
use App\Models\User;
use App\Notifications\ActividadTrajeTipico;
use App\Models\Notifications;
use DateTime;
use App\Notifications\TrajeTipicoEliminado;

class ActividadesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(
            'getActiveSections',
            'registerResumenes',
            'getInfoResumen',
            'registerFotografia',
            'getInfoFotografia',
            'registerTrajeTipico',
            'cancelarTrajeTipico',
            'ReenviarTrajeTipico',
            'getInfoTrajeTipico'
        );
        //$this->middleware('checkRole:7')->only('getTotalInfo');
    }

    public function getInfoTrajeTipico(Request $request){
        $user = $request->user();
        $info = [];
        $inscripcion = InscripcionTrajeTipico::where('primer_participante',$user->id)
        ->orWhere('segundo_participante',$user->id)->first();
        try{
            $info['estado'] = $inscripcion->estado->estado;
            $rutaCompleta ='actividades/traje_tipico/reseña/'. $inscripcion->nombre_doc;
            $info['url_doc'] = Functions::searchLinksS3($rutaCompleta);
            $rutaCompleta ='actividades/traje_tipico/mp3/'. $inscripcion->nombre_pista;
            $info['url_pista'] = Functions::searchLinksS3($rutaCompleta);
            $info['NombreParticipante1'] = $inscripcion->primerParticipante->nombres . ' ' . $inscripcion->primerParticipante->apellidos;
            $info['NombreParticipante2'] = $inscripcion->segundoParticipante->nombres . ' ' . $inscripcion->segundoParticipante->apellidos;
            return response()->json(['info'=>$info], 200);
        }catch(\Exception $e){
            return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e], 500);
        }
    }

    public function aceptarPeticion(Request $request){
        $request->validate([
            'id' => 'required:exists:inscripcion_traje,id',
            'primer_participante' => 'required:exists:users,id',
            'segundo_participante' => 'required:exists:users,id',
        ]);
        $id = $request['id'];
        $primer_participante = $request['primer_participante'];
        $segundo_participante = $request['segundo_participante'];
        $inscripcion = InscripcionTrajeTipico::where('segundo_participante',$segundo_participante)
        ->where('id',$id)->where('primer_participante',$primer_participante)->first();
        if($inscripcion->confirmacion_segundo_participante == null){
            $inscripcion->confirmacion_segundo_participante = new DateTime();
            $inscripcion->save();
            $estado = EstadosMexico::find($inscripcion->estado_representante);
            $estado->usado = new DateTime();
            $estado->save();
            $inscripciones_a_eliminar = InscripcionTrajeTipico::where('estado_representante',$inscripcion->estado_representante)->where('primer_participante','!=',$inscripcion->primer_participante)->get();
            foreach($inscripciones_a_eliminar as $inscripcion_a_eliminar){
                $user1 = $inscripcion_a_eliminar->primerParticipante;
                $user2 = $inscripcion_a_eliminar->segundoParticipante;
                $user1->notify(new TrajeTipicoEliminado());
                $user2->notify(new TrajeTipicoEliminado());
                $inscripcion_a_eliminar->delete();
            }
            return response()->json(['message'=>'Se acepto correctamente'], 200);
        }else{
            return response()->json(['error' => 'error', 'message' => 'No se puede aceptar, ya que el segundo participante ya confirmo su participación, contactenos para mas información'], 500);
        }
    }

    public function cancelarTrajeTipico(Request $request){
        $user = $request->user();
        $inscripcion = InscripcionTrajeTipico::where('primer_participante',$user->id)
        ->orWhere('segundo_participante',$user->id)->first();
        if($inscripcion->confirmacion_segundo_participante == null){
            $inscripcion->delete();
            return response()->json(['message'=>'Se cancelo correctamente'], 200);
        }else{
            return response()->json(['error' => 'error', 'message' => 'No se puede cancelar, ya que el segundo participante ya confirmo su participación, contactenos para mas información'], 500);
        }
    }

    public function ReenviarTrajeTipico(Request $request){
        $user = $request->user();
        $inscripcion = InscripcionTrajeTipico::where('primer_participante',$user->id)
        ->orWhere('segundo_participante',$user->id)->first();
        if($inscripcion->confirmacion_segundo_participante == null){
            $segundo_participante = User::find($inscripcion->segundo_participante);
            $segundo_participante->notify(new ActividadTrajeTipico($inscripcion));
            return response()->json(['message'=>'Se reenvio correctamente'], 200);
        }else{
            return response()->json(['error' => 'error', 'message' => 'No se puede reenviar, ya que el segundo participante ya confirmo su participación, contactenos para mas información'], 500);
        }
    }

    public function registerTrajeTipico(Request $request){
        $user = $request->user();
        $request->validate([
                'estado' => 'required:exists:estados_mexico,estado',
                'file_reseña' => 'required:File',
                'file_mp3' => 'required:File',
                'compañero'=> 'required:exists:users,id',
        ]);
        $estado = $request['estado'];
        if(!User::find($request['compañero'])){
            return response()->json(['error' => 'error', 'message' => 'Parece que el numero de congresista del segundo participante no existe.'], 500);
        }
        if(EstadosMexico::where('estado',$estado)->exists()){
            $estado = EstadosMexico::where('estado',$estado)->first();
            if($estado->usado){
                return response()->json(['error' => 'error', 'message' => 'Estado ya registrado'], 500);
            }
            $estado = $estado->id;
            $compañero = $request['compañero'];
            if(InscripcionTrajeTipico::where('primer_participante',$user->id)->exists() || InscripcionTrajeTipico::where('segundo_participante',$user->id)->exists()){
                return response()->json(['error' => 'error', 'message' => 'Ya estas registrado en esta actividad'], 500);
            }
            if(InscripcionTrajeTipico::where('primer_participante',$compañero)->exists() || InscripcionTrajeTipico::where('segundo_participante',$compañero)->exists()){
                return response()->json(['error' => 'error', 'message' => 'Tu compañero ya esta registrado en esta actividad'], 500);
            }
            $inscripcion = InscripcionTrajeTipico::create([
                'estado_representante' => $estado,
                'primer_participante' => $user->id,
                'segundo_participante' => $compañero,
            ]);
            try{
                $file_reseña = $request->file('file_reseña');
                $nombre = 'reseña_' . $user->id ;
                $carpeta = 'actividades/traje_tipico/reseña/';
                $nombre_doc = Functions::upS3Services($file_reseña, $carpeta, $nombre);
                $inscripcion->nombre_doc = $nombre_doc;
                $file_mp3 = $request->file('file_mp3');
                $nombre = 'mp3_' . $user->id ;
                $carpeta = 'actividades/traje_tipico/mp3/';
                $nombre_doc = Functions::upS3Services($file_mp3, $carpeta, $nombre);
                $inscripcion->nombre_pista = $nombre_doc;
                $inscripcion->save();
                
                $segundo_participante = User::find($compañero);
                $segundo_participante->notify(new ActividadTrajeTipico($inscripcion));
                Notifications::create([
                    'user_id' => $segundo_participante->id,
                    'mensaje'=>"Se te ha invitado a participar en la actividad de traje tipico, por favor revisa tu correo electronico para confirmar tu participación.",
                ]);
                return response()->json(['message'=>'Se preregistro correctamente, esperando confirmación del segundo compañero'], 200);
            }catch(\Exception $e){
                $inscripcion->delete();
                return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e->getTraceAsString()], 500);
            }
        }else{
            return response()->json(['error' => 'error', 'message' => 'Estado no encontrado'], 500);
        }
    }

    public function getEstados(){
        $estados = EstadosMexico::where('usado',null)->get();
        return response()->json(['estados' => $estados], 200);
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

        $traje_tipico = InscripcionTrajeTipico::select('confirmacion_segundo_participante')->where('primer_participante', $user->id)->orWhere('segundo_participante', $user->id)->first();

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
