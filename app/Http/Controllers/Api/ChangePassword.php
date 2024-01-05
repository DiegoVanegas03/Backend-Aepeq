<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\PersonalResetTokens;
use DateTime;


class ChangePassword extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only(
            'changePassword',
            'verificatePassword'
        );
    }

    public function evaluateTokenAccess(Request $request){
        $request->validate([
            'token' => 'required|string',
        ]);
        $token = PersonalResetTokens::where('token', $request->token)->first();
        if($token){
            if($token->expires_at < new DateTime()){
                $token->delete();
                return response()->json(['error' => 'El token a expirado'], 500);
            }else{
                return response()->json(['message' => 'Token valido'],200);
            }
        }else{
            return response()->json(['error' => 'Token no valido', 'message'=>'Parece que el token no es valido, por favor solicita otro correo electronico'],500);
        }
    }

    public function sendEmail(Request $request){
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' =>'error', 'message'=>'Este correo no se encuentra registrado'], 500);
        }

        $expiration = new DateTime('+1 hours');

        if(PersonalResetTokens::where('email', $user->email)->exists()){
            $PersonaResetTokens = PersonalResetTokens::where('email', $user->email)->first();
            $PersonaResetTokens->token = uniqid();
            $PersonaResetTokens->expires_at = $expiration;
            $PersonaResetTokens->save();
        }else{
            $PersonaResetTokens = PersonalResetTokens::create([
                'email' => $user->email,
                'token' => uniqid(),
                'expires_at' => $expiration,
            ]);
        }
        $url = 'https://aepeq.mx/recoverypwd?token='.$PersonaResetTokens->token.'&nombres='.$user->nombres;
        $user->notify(new PasswordReset($url));

        return response()->json(['message' => 'Se ah enviado un correo electronico.']);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed',
        ]);
        $token = PersonalResetTokens::where('token', $request->token)->first();

        if (!$token) {
            return response()->json(['error' => 'error', 'message' => 'El token no es valido'], 500);
        }
        if($token->expires_at < new DateTime()){
            $token->delete();
            return response()->json(['error' => 'error', 'message'=>'El token a expirado'], 500);
        }

        $user = User::where('email', $token->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        $token->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente'],200);
    }

    public function verificatePassword(Request $request){
        $request->validate([
            'password' => 'required',
        ]);
        $user = $request->user();
        if(Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Contraseña correcta'],200);
        }else{
            return response()->json(['error' => 'error', 'message' => 'Contraseña incorrecta'], 500);
        }
    }

    public function changePassword(Request $request){
        $request->validate([
            'new_password' => 'required|confirmed',
        ]);
        $user = $request->user();

        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente'],200);
    }
}
