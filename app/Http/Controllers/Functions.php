<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Aws\S3\Exception\S3Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use App\Models\User;

class Functions{

    public static function searchLinksS3($nombreDoc){
        try{
            /** @var Storage $s3 */
            $s3 = Storage::disk('s3');
            $url = $s3->temporaryUrl($nombreDoc, now()->addMinutes(20));
            return $url;
        }catch(S3Exception $e){
            throw $e;
        }
    }

    public static function getFile($path){
        try{
            /** @var Storage $s3 */
            $s3 = Storage::disk('s3');
            $file = $s3->get($path);
            return $file;
        }catch(S3Exception $e){
            throw $e;
        }
    }

    public static function upS3Services($file, $carpeta, $nombre){
        try{
            $extension = $file->getClientOriginalExtension();
            $file_name = $nombre .'.'. $extension;
            $file->storeAs($carpeta,$file_name,'s3');
            return $file_name;
        }catch(S3Exception $e){
            throw $e;
        }
    }

    public static function createQr($id){
        $qrCode = QrCode::format('png')
            ->size(300)
            ->merge(public_path('aepeqLogo-md.png'), 0.3, true)
            ->generate('Numero de Congresista:'.$id);
            
            // Codificar el código QR en base64
        $qrCodeBase64 = base64_encode($qrCode);
        return $qrCodeBase64;
    }

    public static function verifiedTokenCordinador($token){
        $decoded = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
        switch($decoded->nombre){
            case 'Facturas':
                return 2;
                break;
            case 'AyudaAsistencia':
                return  3;
                break;
            case 'Comite:TrajeTipico':
                return  4;
                break;
            case 'Comite:Fotografia':
                return  5;
                break;
            case 'Comite:Academico':
                return  6;
                break;
            case 'administrador':
                return  7;
                break;
            default:
                return 1;
                break;
            }
    }

    public static function generate_constancia(User $user,$imageURL,$hoja,$folio,$rutaCompleta,$mode){
        $pdf = Pdf::loadView('constancias.canvas');
        $pdf->setPaper('a4','landscape');
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $w = $canvas->get_width(); 
        $h = $canvas->get_height();
        $canvas->image($imageURL, 0, 0, $w, $h);
        $font_b = $pdf->getFontMetrics()->get_font("helvetica", "bold");
        $font_n = $pdf->getFontMetrics()->get_font("helvetica");

        mb_internal_encoding("UTF-8");
        // Convertir a mayúsculas utilizando mb_strtoupper
        $nombre_completo = mb_strtoupper($user->nombres) . ' ' . mb_strtoupper($user->apellidos);

        $length_nombre = ((int) mb_strlen($nombre_completo,'UTF-8'))*13.175;
        $canvas->text($w/2-$length_nombre/2, $h/2-7.5, $nombre_completo, $font_b, 22, array(0,0,0));

        if($mode==='taller'){
            $canvas->text(70, $h-63, str_pad($hoja, 4, '0', STR_PAD_LEFT), $font_n, 13.5, array(0,0,0));
            $canvas->text(70, $h-47, str_pad($folio, 4, '0', STR_PAD_LEFT), $font_n, 13.5, array(0,0,0));
        }else{
            $canvas->text(119, $h-53, str_pad($hoja, 4, '0', STR_PAD_LEFT), $font_n, 13.5, array(0,0,0));
            $canvas->text(119, $h-37, str_pad($folio, 4, '0', STR_PAD_LEFT), $font_n, 13.5, array(0,0,0));
        }
                    
        $content = $pdf->output();
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, $content);
        $nombre = 'constancia_'.$folio;
        $file = new UploadedFile($tempFile, 'undefined.pdf');
        $nombre_doc = Functions::upS3Services($file,$rutaCompleta,$nombre);
        unlink($tempFile);
        return $nombre_doc;
    }

    public static function generate_constancia_bajo_agua($nombre_completo,$imageURL,$hoja,$folio,$rutaCompleta){
        $pdf = Pdf::loadView('constancias.canvas');
        $pdf->setPaper('a4','landscape');
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $w = $canvas->get_width(); 
        $h = $canvas->get_height();
        $canvas->image($imageURL, 0, 0, $w, $h);
        $font_b = $pdf->getFontMetrics()->get_font("helvetica", "bold");
        $font_n = $pdf->getFontMetrics()->get_font("helvetica");

        mb_internal_encoding("UTF-8");
        // Convertir a mayúsculas utilizando mb_strtoupper
        $nombre_completo = mb_strtoupper($nombre_completo);

        $length_nombre = ((int) mb_strlen($nombre_completo,'UTF-8'))*13.175;
        $canvas->text($w/2-$length_nombre/2, $h/2-7.5, $nombre_completo, $font_b, 22, array(0,0,0));

        $canvas->text(70, $h-63, str_pad($hoja, 4, '0', STR_PAD_LEFT), $font_n, 13.5, array(0,0,0));
        $canvas->text(70, $h-47, str_pad($folio, 4, '0', STR_PAD_LEFT), $font_n, 13.5, array(0,0,0));
                    
        $content = $pdf->output();
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, $content);
        $nombre = 'constancia_'.$folio;
        $file = new UploadedFile($tempFile, 'undefined.pdf');
        $nombre_doc = Functions::upS3Services($file,$rutaCompleta,$nombre);
        unlink($tempFile);
        return $nombre_doc;
    }

}


