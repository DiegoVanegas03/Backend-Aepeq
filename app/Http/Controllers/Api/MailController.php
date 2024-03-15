<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\EmailRegister;
use App\Notifications\RecuerdoQr;
use Barryvdh\DomPDF\Facade\Pdf;

class MailController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only('resendEmail');
    }

    public function SendMailPersonalizate(Request $request){
        $request->validate([
            'message'=> 'required|string',
        ]);

        $users = User::all();

        foreach($users as $item){
            
        }
    }

    public function mailRecordatorio(Request $request){
        try{
            $request->validate([
                'inicio'=>'required',
                'final'=>'required',
            ]);

            $inicio  = intval($request['inicio']);
            $final  = intval($request['final']);
            $count = User::count();
            $users = User::skip($inicio-1)->take($final-$inicio+1)->get();

            foreach($users as $user){
                $congresista = [
                    'qr_image' => 'data:image/png;base64,'.$user->qr_code,
                    'nombre'=>$user->nombres,
                    'apellido'=>$user->apellidos,
                    'numero'=>$user->id,
                ];
                $pdf = PDF::loadView('congresista.recordatorio_qr', compact('congresista'));
                $pdf->setPaper('A4'); 
                $pdf->setOption('chroot',realpath(''));
                $user->notify(new RecuerdoQr($pdf));
            }
            return response()->json(['message'=>'Recordatorio enviado','total_usuario'=>$count],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage(),'descripcion'=>$e->getTraceAsString()],500);
        }
    }


    public function verifiedMail(Request $request){
        $user_id = $request['id'];
        $hash = $request['hash'];
        $user = User::findOrFail($user_id);

        $hash_user = sha1($user->email);

        if($hash_user === $hash){
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
            return response()->json(['message' => 'Email verified successfully'],200);
        }else{
            return response()->json(['error' => 'Hash erroneo'],500);
        }
    }

    public function resendEmail(Request $request){
        $user = $request->user();
        try{
            if (!$user->hasVerifiedEmail()) {
                $hash = sha1($user->email);
                $url = 'https://aepeq.mx/verifyMail?id='.$user->id.'&hash='.$hash . '&name='. $user->nombres;
                $user->notify(new EmailRegister($url));
            }
            $message ='Correo de confirmación enviado correctamente';
            return response()->json(['message' => $message],200);
        }catch(\Exception $e){
            return response()->json(['message' => 'Error al enviar el correo', 'detail'=>$e->getTraceAsString()],500);
        }
    }
}
