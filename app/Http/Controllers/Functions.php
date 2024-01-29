<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Aws\S3\Exception\S3Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
}


