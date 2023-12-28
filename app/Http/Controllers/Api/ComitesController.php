<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Comite;
use App\Http\Controllers\Controller;

class ComitesController extends Controller
{
    public function getTotalInfo()
    {
        return Comite::all();
    }
}
