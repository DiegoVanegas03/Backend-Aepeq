<?php


namespace App\Http\Controllers\Api;

use App\Models\Promotor;
use App\Http\Requests\PromotoresRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TokensPromotores;
use App\Http\Controllers\Functions;
use App\Models\FacturaPromotor;
use App\Models\TokensRegistro;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\SendTicketRegistroPromotor;
use DateTime;

class PromotoresController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->only(
            'getTotalInfo',
            'generateUrl',
            'getTokens',
            'cleanTokens',
            'getPromotor',
            'getTokensRegistro'
        );
        $this->middleware('checkRole:7')->only(
            'getTotalInfo',
            'generateUrl',
            'getTokens',
            'cleanTokens',
            'getPromotor',
            'getTokensRegistro'
        );
    }

    public function getTotalInfo(){
        $promotores = Promotor::all();
        return response()->json(['promotores' => $promotores], 200);
    }

    public function getNombres(){
        return Promotor::all('nombre');
    }

    public function generateUrl(){
        $expiration = new DateTime('+3 hours');
        $token = uniqid();
        $token = TokensPromotores::create([
            'token' => $token,
            'fecha_expiracion' => $expiration,
        ]);
        $url = '/becasregistro?token=' . $token->token ;
        return response()->json(['url' => $url], 200);
    }
    
    public function getTokens(){
        $tokens = TokensPromotores::all();
        return response()->json(['tokens' => $tokens], 200);
    }

    public function cleanTokens(){
        $tokens = TokensPromotores::all();
        foreach($tokens as $token){
            if($token->fecha_expiracion < new DateTime()){
                $token->delete();
            }
        }
        return response()->json(['message' => 'Tokens Limpiados'], 200);
    }

    public function evaluateTokenAccess(Request $request){
        $token = TokensPromotores::where('token', $request->token)->first();
        if($token){
            if($token->fecha_expiracion > new DateTime()){
                return response()->json(['message' => 'Token Valido'], 200);
            }else{
                return response()->json(['error' => 'Token Expirado'], 500);
            }
        }else{
            return response()->json(['error' => 'Token Invalido'], 500);
        }
    }

    public function register(PromotoresRequest $request){
        $data = $request->validated();

        $tokenUrl = TokensPromotores::where('token', $data['tokenUrl'])->first();
        if($tokenUrl){
            $promotor = Promotor::create([
                'email'=> $data['mail'],
                'nombre'=> $data['nombre'],
                'numero_de_becas' => $data['numero_de_becas'],
                'precio_por_beca' => $data['precio_por_beca'],
                'precio_total' => $data['precio_total'],
            ]);
            try{
                $file = $request->file('comprobante_de_pago');
                $nombre = 'comprobante_'. $promotor->id;
                $carpeta = 'promotores';
                $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                $promotor->comprobante_de_pago = $nombre_doc;
                $promotor->save();
                if($data['factura'] === 'Si'){
                    if($request->hasFile('cfdi')){  
                        $file = $request->file('comprobante_de_pago');
                        $nombre = 'cfdi_'. $promotor->id;
                        $carpeta = 'facturas/promotores/CFDI';
                        $nombre_doc = Functions::upS3Services($file, $carpeta, $nombre);
                        FacturaPromotor::create([
                            'promotor_id' => $promotor->id,
                            'cfdi' => $nombre_doc,
                        ]);
                    }
                }
                $beneficiados = json_decode($data['beneficiados'], true);
                foreach ($beneficiados as $beneficiado) {
                    TokensRegistro::create([
                        'token_de_registro'=>uniqid(),
                        'tipo_inscripcion'=>$beneficiado['select'],
                        'nombre'=>$beneficiado['nombre'],
                        'promotor_id'=>$promotor->id,
                    ]);
                }
                $domain = 'http://localhost:3000/registro';
                $tokens = TokensRegistro::where('promotor_id', $promotor->id)->get();
                foreach($tokens as $token){
                    $metadata ='token='.$token->token_de_registro.'&tipo_inscripcion='.$token->tipo_inscripcion;
                    $url = $domain.'?metadata='.base64_encode($metadata);
                    $token['url'] = $url;
                }
                $nombre = $promotor->nombre;
                $pdf_preview = Pdf::loadView('promotores.ticket', compact('tokens', 'nombre'))->setPaper('a4', 'landscape');
                $pdf_output = $pdf_preview->output();
                $pdf = base64_encode($pdf_output);
                $tokenUrl->delete();
                $promotor->notify(new SendTicketRegistroPromotor('Ticket de Registro Promotor',$promotor->nombre,$pdf_output));
                return response()->json(['message' => 'Registro Exitoso', 'pdf'=>$pdf], 200);
            }catch(\Exception $e){
                $promotor->delete();
                return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e], 500);
            }
        }else{
            return response()->json(['error' => 'invalido', 'message'=>'token invalido'], 500);
        }
    
    }

    public function getPromotor(Request $request){
        $id = $request['idPromotor'];
        $promotor = Promotor::find($id);

        $newPromotor = [
            'Correo' => $promotor->email,
            'Nombre' => $promotor->nombre,
            'Numero de becas' => $promotor->numero_de_becas,
            'Precio por becas' => $promotor->precio_por_beca,
            'Precio Total' => $promotor->precio_total,
            'Comprobante de pago' => $promotor->comprobante_de_pago,
        ];

        try{
            $carpeta = 'promotores/';
            $links['Comprobante de pago'] = Functions::searchLinksS3($carpeta . $promotor->comprobante_de_pago);
            
            $factura = FacturaPromotor::where('promotor_id',$id)->first();
            if($factura){
                $newPromotor['CFDI'] = $factura->cfdi;
                $carpeta = 'facturas/promotores/CFDI/';
                $links['CFDI'] = Functions::searchLinksS3($carpeta . $factura->cfdi);
                $newPromotor['Factura Realizada'] = $factura->factura_realizada;
                if($factura->factura_realizada === 'Si'){
                    $newPromotor['Factura'] = $factura->factura;
                    $carpeta = 'facturas/promotores/factura/';
                    $links['Factura'] = Functions::searchLinksS3($carpeta . $factura->factura);
                }
            }else{
                $newPromotor['Factura'] = 'No';
            }
            $newPromotor['Hora de Creacion'] = $promotor->created_at;
            $newPromotor['Hora de Actualizacion'] = $promotor->updated_at;
            $promotor = $newPromotor;
            return response()->json(['promotor' => $promotor, 'links' => $links], 200);

        }catch (\Exception $e) {
            return response()->json(['error' => 'error', 'message' => $e->getMessage(),'details'=>$e->getTraceAsString()], 500);
        }
    }

    public function getTokensRegistro(Request $request){
        $id_promotor = $request['idPromotor'];
        $domain = 'http://localhost:3000/registro';
        $tokens = TokensRegistro::where('promotor_id', $id_promotor)->get();
        foreach($tokens as $token){
            $metadata ='token='.$token->token_de_registro.'&tipo_inscripcion='.$token->tipo_inscripcion;
            $url = $domain.'?metadata='.base64_encode($metadata);
            $token['url'] = $url;
        }
        return response()->json(['tokens' => $tokens], 200);
    }
}
