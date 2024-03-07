<?php

namespace App\Http\Controllers\Api;

use App\Exports\Fotografia;
use App\Exports\TrajeTipicoExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InscripcionResumenes;
use App\Models\InscripcionFotografia;
use App\Models\InscripcionTrajeTipico;
use App\Models\ColaboradorResumenes;
use App\Models\ComentariosResumenes;
use App\Http\Requests\ResumenesRequest;
use App\Http\Controllers\Functions;
use App\Http\Requests\FotografiaRequest;
use App\Models\EstadosMexico;
use App\Models\User;
use App\Notifications\ActividadTrajeTipico;
use DateTime;
use App\Notifications\TrajeTipicoEliminado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Casts\Json;
use App\Notifications\CartaDeAceptacion;
use App\Notifications\CorreccionesResumen;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;


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
            'getInfoTrajeTipico',
            'aceptacionResumen',
            'getResuemenes',
            'pedir_correciones',
            'solicitar_extension',
            'getFotografias',
            'descargarExcel',
            'getTrajeTipico',
            'descargarExcelTrajeTipico',
        );
        $this->middleware('checkRole:7,5')
        ->only(
            'aceptacionResumen',
            'getResuemenes', 
            'pedir_correciones',
            'solicitar_extension'
        );
        $this->middleware('checkRole:7,6,4')
        ->only(
            'getFotografias',
            'descargarExcel',
            'getTrajeTipico',
            'descargarExcelTrajeTipico',
        );
    }

    public function descargarExcelTrajeTipico(){
        return Excel::download(new TrajeTipicoExport(), 'archivo.xlsx');
    }

    public function getTrajeTipico(){
        $congresistas = InscripcionTrajeTipico::select(
            'primer_participante',
            'segundo_participante',
            'estado_representante',
            'nombre_doc',
            'nombre_pista',
            'confirmacion_segundo_participante',
        )->get();
        $carpeta_reseña = 'actividades/traje_tipico/reseña/';
        $carpeta_mp3 = 'actividades/traje_tipico/mp3/';
        foreach($congresistas as $item){

            $piv_nombre_uno = $item->primerParticipante->nombres.' '.$item->primerParticipante->apellidos;
            unset($item->primerParticipante);        
            $piv_nombre_dos = $item->segundoParticipante->nombres.' '.$item->segundoParticipante->apellidos;
            unset($item->segundoParticipante);
            $item['estado_representante'] = $item->estado->estado;
            unset($item->estado);
            $item['primer_participante'] = $piv_nombre_uno;
            $item['segundo_participante'] = $piv_nombre_dos;
            $item['url_doc'] = Functions::searchLinksS3($carpeta_reseña.$item->nombre_doc);
            $item['url_mp3'] = Functions::searchLinksS3($carpeta_mp3.$item->nombre_pista);
        }
        return response()->json(compact('congresistas'),200);
    }


    public function descargarExcel(){
        return Excel::download(new Fotografia(), 'archivo.xlsx');
    }

    public function getFotografias(){
        $registros = InscripcionFotografia::select(
            'id',
            'user_id',
            'nombre_fotografia',
            'lugar_y_fecha',
            'nombre_fotografia',
            'descripcion',
            'documento'
        )->get();
        
        $carpeta = 'actividades/fotografia/';
        foreach($registros as $item){
            $item['nombre_completo'] = $item->user->nombres.' '. $item->user->apellidos;
            $item['url'] = Functions::searchLinksS3($carpeta.$item->documento);
            unset($item['user']);
        }
        return response()->json(['congresistas'=>$registros],200);
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

    public function getResumenes(){
        $congresistas['no_realizadas'] = InscripcionResumenes::where('estado_de_revision',0)->select('id','nombre_archivo','nombre_investigador','apellidos_investigador','created_at')->get();
        $congresistas['realizadas'] = InscripcionResumenes::where('estado_de_revision',1)->select('id','nombre_archivo','nombre_investigador', 'apellidos_investigador','dictamen','documento_final','oral/escrito')->get();
        foreach($congresistas['no_realizadas'] as $congresista){
            $rutaCompleta ='actividades/resumenes/'. $congresista->nombre_archivo;
            $congresista['url'] = Functions::searchLinksS3($rutaCompleta);
            $congresista['colaboradores'] = ColaboradorResumenes::where('inscripcion_id',$congresista->id)->get();
        }
        foreach($congresistas['realizadas'] as $congresista){
            $congresista['oral_escrito'] = $congresista['oral/escrito'];
            $rutaCompleta ='actividades/resumenes/'. $congresista->nombre_archivo;
            $congresista['url'] = Functions::searchLinksS3($rutaCompleta);
            $congresista['url_dictamen'] = Functions::searchLinksS3('actividades/resumenes/dictamenes/'.$congresista->dictamen);
            $congresista['url_documento_final'] = $congresista->documento_final ? Functions::searchLinksS3('actividades/resumenes/documentos_finales/'.$congresista->documento_final) : null;
            $congresista->comentarios = ComentariosResumenes::select('comentario')->where('inscripcion_id', $congresista->id)->get();
        }
        return response()->json(compact('congresistas'),200);
    }

    public function pedir_correciones(Request $request){
        $request->validate([
            'correcciones' => 'required|string',
            'id_inscripcion' => 'required|integer',
        ]);
        $inscripcion = InscripcionResumenes::find($request->id_inscripcion);
        $correcciones = Json::decode($request->correcciones);
        $inscripcion->user->notify(new CorreccionesResumen($correcciones, $inscripcion->id));
        return response()->json(['message'=>'Se solicito correctamente las correcciones.'], 200);
    }

    public function uploadExtensioFile(Request $request){
        $request->validate([
            'id_inscripcion' => 'required|integer',
            'id_usuario' => 'required|integer',
            'segunda_opcion'=>'required|string',
            'file' => 'required|File',
        ]);
        $id_inscripcion = $request->id_inscripcion;
        $id_usuario = $request->id_usuario;
        $file = $request->file('file');
        $nombre = 'documento_final_'.$id_inscripcion;
        $rutaCompleta ='actividades/resumenes/documentos_finales/';
        $nombre_doc = Functions::upS3Services($file,$rutaCompleta,$nombre);
        $inscripcion = InscripcionResumenes::where('id',$id_inscripcion)->where('user_id', $id_usuario)->first();
        $inscripcion->documento_final = $nombre_doc;
        if($request->segunda_opcion === 'false'){
            $inscripcion['oral/escrito'] = $request->modalidad;
        }
        $inscripcion->save();
        return response()->json(['message'=>'Se subio correctamente el documento final.'], 200);
    }

    public function solicitar_extension(Request $request){
        $request->validate([
            'id_inscripcion' => 'required|integer',
        ]);
        $id_inscripcion = $request->id_inscripcion;
        $inscripcion = InscripcionResumenes::find($id_inscripcion);
        $rutaCompleta ='actividades/resumenes/dictamenes/'.$inscripcion->dictamen;
        $content = Functions::getFile($rutaCompleta);
        $inscripcion->user->notify(new CartaDeAceptacion($content,$inscripcion->dictamen, $inscripcion->id));
        return response()->json(['message'=>'Se solicito correctamente las correcciones.'], 200);
    }

    public function aceptacionResumen(Request $request){
        $request->validate([
            'id_inscripcion' => 'required|integer',
            'titulo'=> 'required|string',
            'observaciones' => 'required|string',
        ]);
        $id_inscripcion = $request->id_inscripcion;
        $comentarios = $request->observaciones;

        $inscripcion = InscripcionResumenes::find($id_inscripcion);
        $colaboradores = ColaboradorResumenes::where('inscripcion_id',$inscripcion->id)->get();
        $inscripcion->user;
        $comentarios = Json::decode($comentarios) ? Json::decode($comentarios) : null;
        foreach($comentarios as $comentario){
            ComentariosResumenes::create([
                'inscripcion_id'=>$inscripcion->id,
                'comentario'=>$comentario,
            ]);
        }
        $inscripcion->estado_de_revision = 1;
        //Formato de tiempó
        $months = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];    
        $date = new DateTime();
        $date = $date->format('j') . ' de ' . $months[$date->format('F')] . ' ' . $date->format('Y');
        //Fin formato de tiempo
        $titulo = $request->titulo;
        $pdf = PDF::loadView('resumenes.aceptacion', compact('inscripcion', 'colaboradores','date','comentarios','titulo'));
        $pdf->setPaper('A4'); 
        $pdf->setOption('chroot',realpath(''));
        $canvas = $pdf->getCanvas(); 
        
        $w = $canvas->get_width(); 
        $h = $canvas->get_height(); 
        
        $imageURL ='FONDO.jpg';  
        $firmaURL = 'Firma diana.png'; 
        $canvas->image($imageURL, 0, 0, $w, $h);
        $canvas->image($firmaURL, $w/2-190/2, $h-185, 190,80);
 
        $pdf->render();

        $content = $pdf->output();
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, $content);
        $nombre = 'dictamen_'.$inscripcion->user->id;
        $rutaCompleta ='actividades/resumenes/dictamenes/';
        $file = new UploadedFile($tempFile, 'undefined.pdf');
        $nombre_doc = Functions::upS3Services($file,$rutaCompleta,$nombre);
        unlink($tempFile);
        $inscripcion->dictamen = $nombre_doc;
        $inscripcion->user->notify(new CartaDeAceptacion($pdf->output(),$nombre, $inscripcion->id));
        $inscripcion->save();
        return response()->json(['message'=>'Se envio correctamente la carta dictamen.'], 200);
    }
}
