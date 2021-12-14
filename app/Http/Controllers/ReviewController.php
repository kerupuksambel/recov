<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function add($id_place, Request $request)
    {
        DB::table('reviews')
        ->insert([
            'id_place' => $id_place,
            'komentar' => $request->komentar,
            'rating' => $request->rating
        ]);

        return redirect('/')->with('msg', 'Review berhasil ditambahkan.');
    }
}
