<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function detail($id_place)
    {
        return response()->json(['isFound' => FALSE]);
    }
}
