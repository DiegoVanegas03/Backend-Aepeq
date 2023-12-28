<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Programa;

class ProgramaController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth:sanctum')->only('getTotalInfo');
        //$this->middleware('checkRole:7')->only('getTotalInfo');
    }

    public function getPonencias(){
        $dia14 = Programa::where('dia',14)->get()->map(function ($pr) {
            $keysToFilter = array_flip(['created_at','updated_at']);
            $pr = array_diff_key($pr->toArray(), $keysToFilter);
            return $pr;
        });
        $dia15 = Programa::where('dia',15)->get()->map(function ($pr) {
            $keysToFilter = array_flip(['created_at','updated_at']);
            $pr = array_diff_key($pr->toArray(), $keysToFilter);
            return $pr;
        });
        $dia16 = Programa::where('dia',16)->get()->map(function ($pr) {
            $keysToFilter = array_flip(['created_at','updated_at']);
            $pr = array_diff_key($pr->toArray(), $keysToFilter);
            return $pr;
        });
        return response()->json(['dia14'=>$dia14, 'dia15'=>$dia15, 'dia16'=>$dia16], 200);
    }
}
