<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Taller;
use App\Http\Controllers\Functions;
use App\Events\UserLoggedOut;
use App\Notifications\PlayAccount;
use App\Notifications\PauseAccount;
use App\Notifications\EmailRegister;


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
            'getDataSideBar'
        );
        $this->middleware('checkRole:7')->only(
            'total_users_info',
            'total_info_admin',
            'pause_register',
            'play_register',
            'edit_user_info',
            'resend_mail_confirmation',
            'getDataSideBar'
        );
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
                case "tipo_inscripcion":
                    $user[$campo] = $info;
                    if($info === "Socios" || $info === "Escuelas"){
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
                        }else{
                            return response()->json(['error'=>'error', 'message'=>"Falta documento"],500);
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
                    $info = trim($info);
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
