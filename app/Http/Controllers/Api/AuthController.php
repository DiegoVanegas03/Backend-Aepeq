<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\FacturaUsuario;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TokensRegistro;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Functions;
use App\Events\UserLoggedOut;
use App\Events\UpdateRegistersAdmin;
use App\Models\AsistenciaGeneral;
use App\Notifications\EmailRegister;
use App\Models\ConstanciaGeneral;
use App\Models\Evaluacion;
use DateTime;


class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only('partial_info', 'get_constancia','total_info', 'logout','register_evaluacion');
    }

    public function register_evaluacion(Request $request){
        $request->validate([
            'selected_q'=>'required|string',
            'response_open_q' =>'required|string',
        ]);
        $user = $request->user();
        $selected_q = $request['selected_q'];
        $response_open_q = json_decode($request['response_open_q']);
        Evaluacion::create([
            'user_id'=>$user->id,
            'repuestas_select'=>$selected_q,
            'mas_gustado'=>$response_open_q[0],
            'menos_gustado'=>$response_open_q[1],
            'mejoras'=>$response_open_q[2],
        ]);
        $constancia = ConstanciaGeneral::where('user_id',$user->id)->first();
        $rutaCompleta = '/constancias/general/'.$constancia['nombre_doc'];
        $url = Functions::searchLinksS3($rutaCompleta);
        return response()->json(compact('url'),200);
    }


    public function get_constancia(Request $request){
        $user = $request->user();
        $constancia = ConstanciaGeneral::where('user_id',$user->id)->first();
        $rutaCompleta = '/constancias/general/'.$constancia['nombre_doc'];
        $url = Functions::searchLinksS3($rutaCompleta);
        return response()->json(compact('url'),200);
    }

    static function userAndLinks(User $user){
        $user = array_filter($user->toArray());
        $keysToFilter = array_flip(['rol', 'email_verified_at', 'created_at','updated_at']);
        $user = array_diff_key($user, $keysToFilter);
        $links = [];
        if($user['tipo_inscripcion'] == 'Escuelas'){
            $links['documento_certificado'] = Functions::searchLinksS3('registro/escuelas/'.$user['documento_certificado']);
        }else if($user['tipo_inscripcion'] == 'Socios'){
            $links['documento_certificado'] = Functions::searchLinksS3('registro/socios/'.$user['documento_certificado']);
        }
        if($user['beca_pago'] == 'Pago'){
            $links['comprobante_pago'] = Functions::searchLinksS3('registro/comprobantes/'.$user['comprobante_pago']);
            if($user['metodo_pago'] == 'transferencia'){
                $factura = FacturaUsuario::where('user_id', $user['id'])->first();
                if($factura){
                    $user['CFDI'] = $factura->cfdi;
                    $links['CFDI'] = Functions::searchLinksS3('facturas/usuarios/CFDI/'.$factura->cfdi);
                    if($factura->factura_realizada === 'Si'){
                        $user['Factura'] = $factura->factura;
                        $links['Factura'] = Functions::searchLinksS3('facturas/usuarios/factura/'.$factura->factura);
                    }else{
                        $user['Factura'] = $factura->factura_realizada;
                    }
                }
            }
        }
        return ['user'=>$user,'links'=> $links];
    }

    public function total_info(Request $request){
        $respuesta = $this->userAndLinks($request->user());
        return response()->json(['user' => $respuesta['user'], 'links' => $respuesta['links']], 200);
    }


    public function partial_info(Request $request){
        $user = $request->user();
        if($user->estado_del_registro === 0){
            $user->currentAccessToken()->delete();
            return response()->json(['error' => 'pausado', 'message' => 'Tu cuenta ha sido pausada, es de vital importancia que contactes a los administradores'], 500);
        }else{
            $token = $user->currentAccessToken();
            $expiration = $token->expires_at;
            // Convertir la fecha de expiración a milisegundos
            $expirationInMilliseconds = $expiration->getTimestamp() * 1000;
            $fechaActual = new DateTime('now');

            if(AsistenciaGeneral::where('user_id',$user->id)->count() > 0){
                if(Evaluacion::where('user_id',$user->id)->exists()){
                    $evaluacion = 2;
                }else{
                    $evaluacion = 1;
                }
            }else{
                $evaluacion = 0;
            }

            $userData = [
                'NumeroCongresista' => $user->id,
                'Nombre' => $user->nombres,
                'Correo' => $user->email,
                'Rol' => $user->rol,
                'time_exp'=> $expirationInMilliseconds,
                'email_verified' => $user->hasVerifiedEmail(),
                'fechaActual'=>$fechaActual->format('Y-m-d'),
                'evaluacion'=>$evaluacion,
            ];
            return response()->json(['user' => $userData], 200);
        }
    }

    public function login(LoginRequest $request){
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if(!$user ){
            return response()->json(['error' => 'Correo no valido', 'message' => 'Este correo no esta registrsado'], 500);
        }
        if(!Hash::check($data['password'], $user->password)){
            return response()->json(['error' => 'Contraseña no valida', 'message' => 'La contraseña es incorrecta'], 500);
        }
        if($user->estado_del_registro === 1){
            return response()->json(['error' => 'revision', 'message' => 'Tu cuenta sigue en revison, agradecemos tu paciencia se te notificara cuando se encuentre activa'], 500);
        }
        if($user->estado_del_registro === 0){
            return response()->json(['error' => 'pausado', 'message' => 'Tu cuenta ha sido pausada, es de vital importancia que contactes a los administradores'], 500);
        }
        $expiration = new DateTime('+3 hours'); // el token expirará en 3 horas
        $tokenResult = $user->createToken('auth_token', ['*'], $expiration);
        $token = $tokenResult->plainTextToken;
        $tokenExpiration = $tokenResult->accessToken->expires_at;
        $expirationInMilliseconds = $tokenExpiration->getTimestamp() * 1000;
        $fechaActual = new DateTime('now');

        if(AsistenciaGeneral::where('user_id',$user->id)->count() > 0){
            if(Evaluacion::where('user_id',$user->id)->exists()){
                $evaluacion = 2;
            }else{
                $evaluacion = 1;
            }
        }else{
            $evaluacion = 0;
        }

        $userData = [
            'NumeroCongresista' => $user->id,
            'Nombre' => $user->nombres,
            'Correo' => $user->email,
            'Rol' => $user->rol,
            'time_exp'=> $expirationInMilliseconds,
            'email_verified' => $user->hasVerifiedEmail(),
            'fechaActual'=>$fechaActual->format('Y-m-d'),
            'evaluacion'=>$evaluacion,
        ];
        return response()->json(['token' => $token, 'user' => $userData], 200);
    }

    public function register(RegisterRequest $request){
        $data = $request->validated();
        //Vrificamos si es que esta usando beca exista el token de registro
        if($data['beca_pago'] == 'Beca'){
            //Verificar que exista en la tabla el token de registro
            $exists = TokensRegistro::where('token_de_registro', $data['token_de_registro'])
                ->where('estado_del_registro', 0)
                ->exists();
            if (!$exists) {
                // El registro no existe regresar error de token no valido
                return response()->json(['error' => 'Token_no_valido', 'message' => 'Token de registro para beca no valido'], 500);
            }else{
                $token = TokensRegistro::where('token_de_registro', $data['token_de_registro'])->first();
                $promotor = $token->promotor;
            } 
        }
        /** @var User $user */
        $user = User::create([ 
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'numero_celular' => $data['numero_celular'],
            'estado_provincia' => $data['estado_provincia'],
            'pais' => $data['pais'],
            'ocupacion' => $data['ocupacion'],
            'lugar_trabajo' => $data['lugar_trabajo'],
            'tipo_inscripcion' => $data['tipo_inscripcion'],
            'beca_pago' => $data['beca_pago'],
        ]);

        try {
            if ($request->hasFile('documento_certificado')) {
                $nombre = 'certificado_' . $user->id ;
                $file = $request->file('documento_certificado');

                if ($data['tipo_inscripcion'] == 'Escuelas') {
                    $user->escuela = $data['escuela'];
                    $carpeta = 'registro/escuelas';                
                } else if ($data['tipo_inscripcion'] == 'Socios') {
                    $user->asociacion = $data['asociacion'];
                    $carpeta = 'registro/socios';                
                }
                $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                $user->documento_certificado = $nombre_doc;
            }

            if($data['beca_pago'] == 'Pago'){
                if ($request->hasFile('comprobante_pago')) {
                    $user->metodo_pago = $data['metodo_pago'];
                    //subida s3
                    $file = $request->file('comprobante_pago');
                    $nombre = 'comprobante_' . $user->id ;
                    $carpeta = 'registro/comprobantes';
                    $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                    //-----//
                    $user->comprobante_pago = $nombre_doc;
                    //Si requiere factura
                    if ($request->hasFile('CFDI')){
                        if($data['metodo_pago'] == 'transferencia'){
                            //subida s3
                            $file = $request->file('CFDI');
                            $nombre = 'CFDI_' . $user->id ;
                            $carpeta = 'facturas/usuarios/CFDI';
                            $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                            //-----//
                            FacturaUsuario::create([
                                'user_id' => $user->id,
                                'cfdi' => $nombre_doc,
                            ]);
                        }
                    }
                }
            }else if($data['beca_pago'] === 'Beca'){
                $user->promotor = $promotor->nombre;
                $user->token_de_registro = $data['token_de_registro'];
                if($promotor->nombre === 'Coordinadores'){
                    $user->rol = Functions::verifiedTokenCordinador($data['token_de_registro']);
                }
                $token->estado_del_registro = 1;
                $token->save();
            }
            // Asignar el código QR codificado en base64 al usuario
            $user->qr_code = Functions::createQr($user->id);
            $user->save();
            $hash = sha1($user->email);
            $url = 'https://aepeq.mx/verifyMail?id='.$user->id.'&hash='.$hash . '&name='. $user->nombres;
            $user->notify(new EmailRegister($url));
            event(new UpdateRegistersAdmin('Nuevo registro', 'Se ha registrado un nuevo usuario'));
            return response()->json(['qrBase64' => $user->qr_code, 'msType'=>"exito" , 'message'=>"Registro guardado correctamente"], 200);
        } catch (\Exception $e) {
            $user->delete();
            return response()->json(['error' => 'error', 'message' => $e->getMessage(), 'details'=>$e->getTraceAsString()], 500);
        }
    }

    public function logout(Request $request){
        $userId = $request->user()->id;
        $request->user()->currentAccessToken()->delete();
        event(new UserLoggedOut($userId));
        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }
}