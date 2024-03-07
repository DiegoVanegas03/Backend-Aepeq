<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Taller;
use App\Models\Mochila;
use App\Http\Controllers\Functions;
use App\Events\UserLoggedOut;
use App\Notifications\PlayAccount;
use App\Notifications\PauseAccount;
use App\Notifications\EmailRegister;
use App\Models\AsistenciaGeneral;
use App\Models\InscripcionTaller;
use App\Models\AsistenciaTaller;
use App\Models\ConstanciaGeneral;
use App\Models\ConstanciaTaller;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Illuminate\Http\UploadedFile;
use DateTime;

class AdminController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only(
            'verify_admin',
            'total_users_info',
            'total_info_admin',
            'pause_register',
            'play_register',
            'edit_user_info',
            'resend_mail_confirmation',
            'getDataSideBar',
            'infoAsistenciaGeneral',
            'tomarAsistencia',
            'registerMochila',
            'infoAsistenciaTaller',
            'tomarAsistenciaTaller',
            'generar_constancias',
            'get_info_constancias_generales',
            'generar_constancias_taller',
            'get_info_constancias_talleres',
            'mergeConstanciasTalleres'
        );
        $this->middleware('checkRole:7')->only(
            'total_users_info',
            'total_info_admin',
            'pause_register',
            'play_register',
            'edit_user_info',
            'resend_mail_confirmation',
            'generar_constancias',
            'get_info_constancias_generales',
            'generar_constancias_taller',
            'get_info_constancias_talleres',
            'mergeConstanciasTalleres'
        );
        $this->middleware('checkRole:2,3,4,5,6,7')->only(
            'infoAsistenciaGeneral',
            'tomarAsistencia',
            'registerMochila',
            'getDataSideBar',
            'infoAsistenciaTaller',
            'tomarAsistenciaTaller'
        );
    }

    public function mergeConstanciasTalleres(Request $request){
        $request->validate([
            'arrayDocuments' => 'required',
        ]);
        $documents = json_decode( $request['arrayDocuments']);
        $rutaCompleta = '/constancias/talleres/';
        $pdf = PDFMerger::init();
        foreach($documents as $item){
            if($item){
                try{
                    $content = Functions::getFile($rutaCompleta.$item);
                    $pdf->addString($content, 'all');
                }catch(\Exception $e){
                    return response()->json([
                        'error' => 'Error al obtener el objeto desde S3',
                        'message' => $e->getMessage()
                    ], 500);
                }
            }
        }
        $pdf->merge();
        $content = $pdf->output();
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, $content);
        $nombre = 'temporal_file';
        $file = new UploadedFile($tempFile, 'undefined.pdf');
        $nombre_doc = Functions::upS3Services($file,'',$nombre);
        unlink($tempFile);
        $url = Functions::searchLinksS3($nombre_doc);
        return response()->json(compact('url'),200);
    }

    public function get_info_constancias_talleres(Request $request){
        $request->validate([
            'idTaller'=>'required',
        ]);
        $usuariosSinConstancia =User::select(
            'users.id',
            'users.nombres',
            'users.apellidos',
                )->join(
                'inscripcion_talleres',
                'users.id',
                '=',
                'inscripcion_talleres.user_id'
                    )->where('inscripcion_talleres.taller_id',$request['idTaller'])->leftJoin(
                        'constancias_taller',
                        'inscripcion_talleres.id',
                        '=',
                        'constancias_taller.ins_taller_id'
                        )->whereNull(
                            'constancias_taller.ins_taller_id'
                            )->orWhere('constancias_taller.correccion',1)
                            ->limit(70)->get();
        $usuariosConConstancia = ConstanciaTaller::select(
            'constancias_taller.folio',
            'constancias_taller.nombre_doc',
            'inscripcion_talleres.user_id',
            )->where(
                'constancias_taller.correccion',0)
                ->join(
                    'inscripcion_talleres',
                    'constancias_taller.ins_taller_id',
                    '=',
                    'inscripcion_talleres.id'
                    )->where('inscripcion_talleres.taller_id',$request['idTaller'])->get();
        $rutaCompleta = '/constancias/talleres/';
        foreach($usuariosConConstancia as $item){
            $item['url_doc'] = Functions::searchLinksS3($rutaCompleta.$item->nombre_doc);
        };
        return response()->json(compact('usuariosSinConstancia','usuariosConConstancia'),200);
    }

    public function generar_constancias_taller(Request $request){
        $request->validate([
            'id_aula'=> 'required|integer',
        ]);
        $folios_hojas=[
            13=>[
                'hoja'=>8,
                'folio'=>141,
            ],
            14=>[
                'hoja'=>9,
                'folio'=>616,
            ],
            15=>[
                'hoja'=>10,
                'folio'=>916,
            ],
        ];
        $taller = Taller::find($request['id_aula']);
        $registros = InscripcionTaller::select(
            'inscripcion_talleres.id',
            'inscripcion_talleres.user_id',
            )->where('inscripcion_talleres.taller_id',$taller->id)->leftJoin(
                'constancias_taller',
                'inscripcion_talleres.id',
                '=',
                'constancias_taller.ins_taller_id'
                )->whereNull(
                    'constancias_taller.ins_taller_id'
                    )->orWhere('constancias_taller.correccion',1)
                    ->limit(70)->get();
        $imageURL ='talleres/con_taller_'.$taller->id.'.jpg';
        $hoja = $folios_hojas[$taller->dia]['hoja'];
        $rutaCompleta ='constancias/talleres/';
        foreach($registros as $item){
            $inscripcion = InscripcionTaller::where('user_id',$item->user->id
                )->where('taller_id',$taller->id)->first();
            $constancia = ConstanciaTaller::where('ins_taller_id',$inscripcion->id)->first();
            
            if($constancia){
                //caso de correcion de constancia
                if($constancia->correccion === 1){
                    Functions::generate_constancia($item->user,$imageURL,$hoja,$constancia->folio,$rutaCompleta,'taller');
                    $constancia->correccion = 0;
                    $constancia->save();
                }
            }else{
                //si exixste un folio que quedo disponible.
                $constancia=ConstanciaTaller::whereNull('ins_taller_id')->where('hoja',$hoja)->first();
                if($constancia){
                    $folio = $constancia->folio;
                    $nombre_doc = Functions::generate_constancia($item->user,$imageURL,$hoja,$constancia->folio,$rutaCompleta,'taller');
                    $constancia->ins_taller_id = $item->id;
                    $constancia->nombre_doc = $nombre_doc;
                    $constancia->save();
                }else{
                    //en caso que no exista la constancia se genera desde 0
                    $folio = $folios_hojas[$taller->dia]['folio'];
                    $count = ConstanciaTaller::join(                
                        'inscripcion_talleres',
                        'constancias_taller.ins_taller_id',
                        '=',
                        'inscripcion_talleres.id'
                        )->join(
                            'talleres',
                            'inscripcion_talleres.taller_id',
                            '=',
                            'talleres.id'
                        )->where('talleres.dia',$taller->dia)->count();
                    $folio =$folio +
                     $count;
                    //hacer left join para contar todos los talleres que tengan el mismo dia que el que se va hacer.
                    $nombre_doc = Functions::generate_constancia($item->user,$imageURL,$hoja,$folio, $rutaCompleta,'taller');
                    ConstanciaTaller::create([
                        'ins_taller_id'=>$item->id,
                        'hoja'=>$hoja,
                        'folio'=>$folio,
                        'nombre_doc'=>$nombre_doc,
                    ]);
                }
            }
        }
        return response()->json(['message'=>'Se generaron con exito las constancias.'],200);
    }

    public function get_info_constancias_generales(Request $request){
        $usuariosSinConstancia = User::select('users.id','users.nombres','users.apellidos')->leftJoin(
            'constancias_general',
            'users.id',
            '=',
            'constancias_general.user_id'
            )->whereNull('constancias_general.user_id')->orWhere('constancias_general.correccion',1)->get();
        $usuariosConConstancia = ConstanciaGeneral::select('id','user_id','nombre_doc')->where('correccion',0)->get();
        $rutaCompleta = '/constancias/general/';
        foreach($usuariosConConstancia as $item){
            $item['url_doc'] = Functions::searchLinksS3($rutaCompleta.$item->nombre_doc);
        };
        return response()->json(compact('usuariosSinConstancia','usuariosConConstancia'),200);
    }

    public function generar_constancias(){
        $users = User::select(
            'users.id',
            'users.nombres',
            'users.apellidos'
            )->leftJoin(
                'constancias_general',
                'users.id',
                '=',
                'constancias_general.user_id'
                )->whereNull(
                    'constancias_general.user_id'
                    )->orWhere('constancias_general.correccion',1)
                    ->limit(70)->get();

        foreach($users as $user){
            $asistencias = AsistenciaGeneral::where('user_id',$user->id)->count();
            if($asistencias >= 1){ // cambiar a 1 antes de deploy

                $rutaCompleta ='constancias/general/';
                $constancia = ConstanciaGeneral::where('user_id',$user->id)->first();
                if($constancia){
                    if($constancia->correccion === 1){
                        $folio = 1240 + $constancia->id -1;
                        $imageURL ='CONSTANCIA.jpg';

                        Functions::generate_constancia($user,$imageURL,11,$folio,$rutaCompleta,'general');
                        $constancia->correccion = 0;
                        $constancia->save();
                    }
                }else{
                    $folio = 1240 + ConstanciaGeneral::count();
                    $imageURL ='CONSTANCIA.jpg';
                    $nombre_doc = Functions::generate_constancia($user,$imageURL,11,$folio,$rutaCompleta,'general');
                    ConstanciaGeneral::create([
                        'user_id'=>$user->id,
                        'nombre_doc'=>$nombre_doc,
                    ]);
                }
            }
        }
        return response()->json([
            'message'=>'Se realizaron exito las constancias'
        ],200);
    }

    public function tomarAsistenciaTaller(Request $request){
        $request->validate([
            'numCongresista' =>'required|integer',
            'diaAsistencia'=>'required|integer',
            'aula'=>'required|string',
        ]);
        $taller = Taller::where('dia', $request->diaAsistencia)->where('aula',$request->aula)->first();
        if(AsistenciaTaller::where('taller_id',$taller->id)->where('user_id',$request->numCongresista)->exists()){
            return response()->json(['error'=>'errorAsistencia', 'message'=>'Parece que ya tomaste asistencia'],500);
        }else{
            AsistenciaTaller::create([
                'taller_id'=>$taller->id,
                'user_id'=>$request['numCongresista'],
            ]);
            return response()->json(['message'=>'Se tomo asistencia con exito'],200);
        }
    }

    public function infoAsistenciaTaller (Request $request){
        $request->validate([
            'numCongresista' =>'required|integer',
            'diaAsistencia'=>'required|integer',
            'aula'=>'required|string',
        ]);
        $fechaActual = new DateTime('now');
        $fechaEspecifica = new DateTime('2024-03-'.$request->diaAsistencia);
        
        if($fechaActual->format('Y-m-d') === $fechaEspecifica->format('Y-m-d')){
            if(AsistenciaGeneral::where('user_id',$request->numCongresista)
            ->where('dia',$request->diaAsistencia)->exists()){
                $taller = Taller::where('dia', $request->diaAsistencia)->where('aula',$request->aula)->first();
                if(AsistenciaTaller::where('user_id',$request->numCongresista)->where('taller_id', $taller->id)->exists()){
                    return response()->json(['error'=>'errorAsistencia', 'message'=>'Ya tomaste asistencia este dia'],500);
                }else{
                    $registro = User::find($request['numCongresista']);
                    if($registro){
                            $user['asistencia_actual'] = AsistenciaTaller::where('taller_id',$taller->id)->count();
                            $user['nombre_taller'] = $taller->nombre_taller;
                            $user['nombre_completo'] = $registro->nombres.' '.$registro->apellidos;
                            $user['taller_id'] = $taller->id;
                            if(InscripcionTaller::where('taller_id',$taller->id)->exists()){
                                return response()->json(compact('user'),200);
                            }else{
                                $error = 'errorInscripcion';
                                $message = 'Parece que este usuario no se encuentra inscrito a ese taller.'; 
                                $collection = InscripcionTaller::where('user_id',$registro->id)->get();
                                foreach($collection as $item){
                                    if($item->taller->dia === (int)$request->diaAsistencia){
                                        $user['taller_inscrito'] = $item->taller->nombre_taller;
                                        $user['aula_inscrita'] = $item->taller->aula;                  
                                    }
                                }  
                                return response()->json(compact('error','message','user'),500);
                            }
                        }else{
                            return response()->json(['message'=>'Tu cuenta se encuentra en pausa o '],500);
                        } 
                }
            }else{
                return response()->json([
                    'error'=>'errorAsistencia',
                    'message'=>'Primero debe tomar asistencia este dia.'
                ],500);
            }            
        }else{
            return response()->json(['error'=>'errorFecha', 'message'=>'Todavia no es el día para tomar asistencia :)'],500);
        }
    }

    public function registerMochila(Request $request){
        $request->validate([
            'numCongresista'=>'required|integer',
            'mochila'=>'required|boolean',  
        ]);
        
        if(Mochila::where('user_id',$request->numCongresista)->exists()){
            return response()->json(['error' =>'errorMochila','message'=>'Parece que este usuario ya recibio su mochila'],500);
        }else{
            Mochila::create([
                'user_id'=> $request->numCongresista,
            ]);
            return response()->json(['message'=>'Se registro correctamente'],200);
        }
    }

    public function  tomarAsistencia(Request $request){
        $request->validate([
            'numCongresista' =>'required|integer',
            'diaAsistencia'=>'required|integer',
            'mochila' => 'boolean',
        ]);

        if(AsistenciaGeneral::where('user_id',$request->numCongresista)
            ->where('dia',$request->diaAsistencia)->exists()){
                return response()->json(['error'=>'error', 'message'=>'Parece que este usuario ya tomo asistencia este día.'],500);
        }else{
            AsistenciaGeneral::create([
                'user_id' => $request->numCongresista,
                'dia'=> $request->diaAsistencia,
            ]);
            if($request->mochila){
                Mochila::create([
                    'user_id'=> $request->numCongresista,
                ]);
            }
            return response()->json(['message'=>'Se tomo la asistencia con exito.'],200);
        }
    }

    public function infoAsistenciaGeneral(Request $request){
        $request->validate([
            'numCongresista' =>'required|integer',
            'diaAsistencia'=>'required|integer'
        ]);
        $fechaActual = new DateTime('now');
        $fechaEspecifica = new DateTime('2024-03-'.$request->diaAsistencia);
        
        if($fechaActual->format('Y-m-d') === $fechaEspecifica->format('Y-m-d')){
            $registro = User::find($request['numCongresista']);
            if($registro){
                $mochila = Mochila::where('user_id',$request->numCongresista)->exists();
                if($registro->estado_del_registro === 1 || $registro->estado_del_registro === 0){
                    return response()->json(['error'=>'errorEstado', 'message'=>'La cuenta de este usuario se encuenta en revision o pausa, favor de mandarlo a la mesa de registro para que lo auxilien :).', 'mochila'=>$mochila],500);
                }else{
                    if(AsistenciaGeneral::where('user_id',$request->numCongresista)
                    ->where('dia',$request->diaAsistencia)->exists()){
                        return response()->json(['error'=>'errorAsistencia', 'message'=>'Parece que este usuario ya tomo asistencia este día.', 'mochila'=>$mochila],500);
                    }else{
                        $user['nombre_completo'] = $registro->nombres.' '.$registro->apellidos;
                        $user['mochila'] = $mochila;
                        return response()->json(compact('user'),200);
                    }
                }
            }else{
                return response()->json(['error'=>'error', 'message'=>'Parece que este usuario no existe'],500);
            }   
        }else{
            return response()->json(['error'=>'errorFecha', 'message'=>'Todavia no es el día para tomar asistencia :)'],500);
        }
        
    }

    public function count_users_asosiaciones(){
        $socios = User::where('tipo_inscripcion', 'Socios')->count();
        $asociaciones = [
            ["value" => "AEPEQ", "label" => "Asociación Estatal Potosina de Enfermería Quirúrgica"],
            ["value" => "CEQCDMXAM", "label" => "Colegio de Enfermeras Quirúrgicas de la Ciudad de México y Área Metropolitana"],
            ["value" => "CEQEC", "label" => "Colegio de Enfermería Quirúrgica del Estado de Colima"],
            ["value" => "CEQDEC", "label" => "Colegio de Enfermería Quirúrgica del Estado de Coahuila"],
            ["value" => "CEMQA", "label" => "Colegio de Enfermería Medico Quirúrgica de Aguascalientes"],
            ["value" => "CEQD", "label" => "Colegio de Enfermería Quirúrgica Duranguense"],
            ["value" => "CLEQ", "label" => "Colegio Leonés de Enfermería Quirúrgica"],
            ["value" => "AEQM", "label" => "Asociación de Enfermería Quirúrgica de Michoacán"],
            ["value" => "CENAQ", "label" => "Colegio de Enfermería Nayarita para la Atención Quirúrgica"],
            ["value" => "CEQNL", "label" => "Colegio de Enfermería Quirúrgica de Nuevo León"],
            ["value" => "CEQES", "label" => "Enfermeras Quirúrgicas Oaxaqueñas"],
            ["value" => "AEQQR", "label" => "Asociación de Enfermería Quirúrgica de Quintana Roo"],
            ["value" => "CEQS", "label" => "Colegio de Enfermería Quirúrgica de Sonora"],
            ["value" => "CTEQ", "label" => "Colegio Tamaulipeco de Enfermería Quirúrgica"],
            ["value" => "CEQEZ", "label" => "Colegio de Enfermería Quirúrgica del Estado de Zacatecas"],
            ["value" => "AEQUIEV", "label" => "Asociación de Enfermeras y Enfermeros Quirúrgicos del Estado de Veracruz"],
            ["value" => "CQEQ", "label" => "Colegio Queretano de Enfermería Quirúrgica"],
            ["value" => "FEMEQ", "label" => "Federación Mexicana de Enfermería Quirúrgica"],
        ];
        $counts = [];
        foreach ($asociaciones as $asociacion) {
            $counts[$asociacion['value']] = User::where('asociacion', $asociacion['value'])->count();
        }
        return response()->json(['socios_totales' => $socios, 'asociaciones' => $counts], 200);
    }

    public function getDataSideBar(){
        $t13 = Taller::where('dia', 13)->select('id','aula','dia')->orderByRaw('LENGTH(aula), aula')->get();
        $t14 = Taller::where('dia', 14)->select('id','aula','dia')->orderByRaw('LENGTH(aula), aula')->get();
        $t15 = Taller::where('dia', 15)->select('id','aula','dia')->orderByRaw('LENGTH(aula), aula')->get();
        return response()->json(['Dia13'=>$t13,'Dia14'=> $t14, 'Dia15'=>$t15], 200);
    }

    public function resend_mail_confirmation(Request $request){
        $user = User::findOrFail($request['id']);
        if($user->email_verified_at != null){
            return response()->json(['message' => "Ya confirmo su correo"], 500);
        }else{
            $hash = sha1($user->email);
            $url = 'https://aepeq.mx/verifyMail?id='.$user->id.'&hash='.$hash . '&name='. $user->nombres;
            $user->notify(new EmailRegister($url));
            return response()->json(['message' => "Correo enviado correctamente"], 200);
        }
    }

    public function edit_user_info(Request $request){
        $id = $request['id_user'];
        $campo = $request['campo'];
        $info = $request['informacion'];
        $escuela = $request['escuela'];
        $asociacion = $request['asociacion'];
        $user = User::findOrFail($id);
        try{
            switch($campo){
                case "apellidos":
                case "nombres":
                    $user[$campo] = $info;
                    $constancia_general = ConstanciaGeneral::where('user_id',$user->id)->first();
                    if($constancia_general){
                        $constancia_general->correccion = 1;
                        $constancia_general->save();
                    }
                    $inscripciones = InscripcionTaller::where('user_id',$user->id)->get();
                    $inscripciones->each(function ($item) {
                        $constancia = ConstanciaTaller::where('ins_taller_id', $item->id)->first();
                        if($constancia){
                            $constancia->correccion = 1;
                            $constancia->save();
                        }
                    });
                    break;
                case "tipo_inscripcion":
                    $user[$campo] = $info;
                    if($info === "Socios" || $info === "Escuelas"){
                        if(!$request->hasFile('documento') && !$user->documento_certificado){
                            return response()->json([
                                'error'=>'error', 
                                'message'=>"Es te usuario no  tiene documento que conste su 
                                            asosiacion o escuela, para el si es NECESARIO que
                                            subas un archivo."
                                ],500);
                        }
                        if($request->hasFile('documento')){
                            $file =  $request->file('documento');
                            $nombre = "certificado_" . $user->id;
                            if ($info == 'Escuelas') {
                                $user->escuela = $escuela;
                                $carpeta = 'registro/escuelas';                
                            } else if ($info == 'Socios') {
                                $user->asociacion = $asociacion;
                                $carpeta = 'registro/socios';                
                            }
                            $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                            $user->documento_certificado = $nombre_doc;
                        }
                    }
                    break;
                case "comprobante_pago":
                case "documento_certificado":
                    if($request->hasFile('documento')){
                        $file =  $request->file('documento');
                        if($campo === "documento_certificado"){
                            $nombre = "certificado_" . $user->id;
                            if($user->tipo_inscripcion === "Socios"){
                                $carpeta = 'registro/socios';
                            }else{
                                $carpeta = 'registro/escuelas';
                            }
                        }else{
                            $nombre = "comprobante_" . $user->id;
                            $carpeta = 'registro/comprobantes';                
                        }
                        $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                        $user[$campo] = $nombre_doc;
                    }else{
                        return response()->json(['error'=>'error', 'message'=>"Falta documento"],500);
                    }
                    break;
                default:
                    $user[$campo] = $info;
                    break;
            }
            $user->save();
            return response()->json(['message'=>'Actualizado correctamente'],200);
        }catch(\Exception $e){
            return response()->json(['error'=>'error', 'message'=>$e->getMessage()],500);
        }

        
    }

    public function total_users_info(){
        $users = User::all()->map(function ($user) {
            $keysToFilter = array_flip(['ocupacion', 'lugar_trabajo', 'escuela', 'asociacion','documento_certificado','comprobante_pago']);
            $user = array_diff_key($user->toArray(), $keysToFilter);
            return $user;
        });
        return response()->json(['users' => $users], 200);
    }

    public function verify_admin(Request $request){
        $user = $request->user();
        if($user->rol > 1){
            return response()->json(['Rol'=>$user->rol], 200);
        }else{
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'No es administrador'], 500);
        }
    }

    public function total_info_admin(Request $request){
        $user = User::where('id', $request['id'])->first();
        $respuesta = AuthController::userAndLinks($user);
        return response()->json(['user' => $respuesta['user'], 'links' => $respuesta['links']], 200);
    }

    public function pause_register(Request $request){
        $userId = $request['id'];
        $user = User::findOrFail($userId);
        if($user){
            $user->pause();
            $user->save();
            if($user->tokens){
                $user->tokens->each(function ($token) {
                    $token->delete();
                });
                event(new UserLoggedOut($userId));
            }
            $user->notify(new PauseAccount());
            return response()->json(['message' => "Registro Pausado Correctamente"], 200);
        }else{
            return response()->json(['message' => "No se encontro el usuario"], 500);    
        }
    }

    public function play_register(Request $request){
        $user = User::findOrFail($request['id']);
        $user->play();
        $user->save();
        $user->notify(new PlayAccount());
        return response()->json(['message' => "Activado Correctamente"], 200);
    }

}
