<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaceController extends Controller
{
    public function detail($id_place)
    {
        $reviews = DB::table('reviews')
        ->select('rating')
        ->where('id_place', '=', $id_place)
        ->get();
        
        if($reviews->count() > 0){
            $reviews_array = $reviews->toArray();
            $rating = array_sum(array_column($reviews_array, 'rating')) / count($reviews->toArray());
            return response()->json([
                'isFound' => TRUE,
                'rating' => $rating
            ]);
        }
        return response()->json(['isFound' => FALSE]);
    }

    public function submit($id_place, Request $request)
    {
        DB::table('reviews')
        ->insert([
            'id_place' => $id_place,
            'komentar' => $request->komentar,
            'rating' => $request->rating
        ]);

        return redirect('/');
    }
}
