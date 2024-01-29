<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FacturaUsuario;
use App\Models\FacturaPromotor;
use App\Http\Controllers\Functions;

class FacturasController extends Controller{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only('getAll');
        $this->middleware('checkRole:7')->only('getAll');
    }

    public function realizarFactura(Request $request){
        $request->validate([
            'id' => 'required|integer',
            'tipo' => 'required|string',
            'factura' => 'required|file',
        ]);
        if($request->tipo == 'congresista'){
            $factura = FacturaUsuario::find($request->id);
            $factura->factura_realizada = 'Si';
            $carpeta = 'facturas/usuarios/factura/';
            $nombre = 'factura_'.$factura->usuario->id;
            $file = $request->file('factura');
            $factura->factura = Functions::upS3Services($file,$carpeta,$nombre);
            $factura->save();
        }else if($request->tipo == 'promotor'){
            $factura = FacturaPromotor::find($request->id);
            $factura->factura_realizada = 'Si';
            $carpeta = 'facturas/promotores/factura/';
            $nombre = 'factura_'.$factura->promotor->id;
            $file = $request->file('factura');
            $factura->factura = Functions::upS3Services($file,$carpeta,$nombre);
            $factura->save();
        }
        return response()->json([
            'message' => 'Factura realizada con exito'
        ],200);
    }

    public function getAll(){
        $congresistas['no_realizadas'] = FacturaUsuario::where('factura_realizada','No')->get();
        $congresistas['realizadas'] = FacturaUsuario::where('factura_realizada','Si')->get();
        foreach($congresistas['no_realizadas'] as $congresista){
            $congresista->usuario;
            $congresista['cfdi_link'] = Functions::searchLinksS3('facturas/usuarios/CFDI/'.$congresista->cfdi);
            $congresista['cp_link'] = Functions::searchLinksS3('registro/comprobantes/'.$congresista->usuario->comprobante_pago);
        }
        foreach($congresistas['realizadas'] as $congresista){
            $congresista->usuario;
            $congresista['factura_link'] = Functions::searchLinksS3('facturas/usuarios/factura/'.$congresista->factura);
        }

        $promotores['no_realizadas'] = FacturaPromotor::where('factura_realizada','No')->get();
        $promotores['realizadas'] = FacturaPromotor::where('factura_realizada','Si')->get();
        foreach($promotores['no_realizadas'] as $promotor){
            $promotor->promotor;
            $promotor['cfdi_link'] = Functions::searchLinksS3('facturas/promotores/CFDI/'.$promotor->cfdi);
            $promotor['cp_link'] = Functions::searchLinksS3('promotores/'.$promotor->promotor->comprobante_de_pago);
        }
        foreach($promotores['realizadas'] as $promotor){
            $promotor->promotor;
            $promotor['factura_link'] = Functions::searchLinksS3('facturas/promotores/factura/'.$promotor->factura);
        }
        return response()->json([
            'congresistas' => $congresistas,
            'promotores' => $promotores
        ],200);
    }
}
