<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\EmailRegister;
use DateTime;

class MailController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only('resendEmail');
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
