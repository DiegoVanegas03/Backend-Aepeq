<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
        $message ='Correo de confirmación enviado correctamente';
        $expiration = 30;
        return response()->json(['message' => $message],200);
    }
}
