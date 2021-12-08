<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaceController extends Controller
{
    public function index($encoded_link)
    {
        $link = base64_decode($encoded_link);
        
        $client = new \GuzzleHttp\Client();
        $response = $client->get($link); 

        $xml = ((string)$response->getBody());
        $loaded = simplexml_load_string($xml);

        $loaded->node[] = "xxx";

        dd($loaded);

        
    }

    public function detail($id_place)
    {
        $reviews = DB::table('reviews')
        ->select('rating', 'komentar')
        ->where('id_place', '=', $id_place);

        
        if($reviews->count() > 0){
            $reviews_array = $reviews->get()->toArray();
            $rating = array_sum(array_column($reviews_array, 'rating')) / count($reviews->get()->toArray());
            
            $comments = $reviews->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
            $comments = array_column($comments, 'komentar');
            
            return response()->json([
                'isFound' => TRUE,
                'rating' => $rating,
                'komentar' => $comments
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
