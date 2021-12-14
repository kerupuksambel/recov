<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        // Get parameters
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'radius' => 'required|numeric',
        ]);
        if($validator->fails()){
            return response()->json(['success' => FALSE], 400);
        }

        if(!$request->amenity) $request->amenity = [];

        $radius = (double)$request->radius / 111;
        $lats = [(double)$request->lat - ($radius/2), (double)$request->lat + ($radius/2)];
        $longs = [(double)$request->lon - ($radius/2), (double)$request->lon + ($radius/2)];

        $amenities = [];
        if(in_array('cafe', $request->amenity)) $amenities[] = 'node[amenity=cafe];';
        if(in_array('restaurant', $request->amenity)) $amenities[] = 'node[amenity=restaurant];';

        // Get data from DB
        $data = Place::select('*')
        ->where('lat', '>=', $lats[0])
        ->where('lat', '<=', $lats[1])
        ->where('long', '>=', $longs[0])
        ->where('long', '<=', $longs[1])
        ->get();

        // Get JSON from Overpass
        $link = 'https://overpass-api.de/api/interpreter?data=[out:json][bbox][timeout:180];('. join('', $amenities) .');out;&bbox='.join(',', [$longs[0], $lats[0], $longs[1], $lats[1]]);
        // dd($link);
        $client = new \GuzzleHttp\Client();
        $response = $client->get($link);

        $json = json_decode($response->getBody(), TRUE);

        // Combine
        foreach ($data as $d) {
            $json['elements'][] = [
                'type' => 'node',
                'id' => $d->id,
                'lat' => $d->lat,
                'lon' => $d->long,
                'tags' => [
                    'amenity' => $d->amenity,
                    'name' => $d->nama
                ]
            ];
        }
        // Get all the ratings
        $ids = array_column($json['elements'], 'id');
        $ratings = Review::selectRaw('COUNT(*) AS count, SUM(rating) AS sum, id_place')
        ->whereIn('id_place', $ids)
        ->groupBy('id_place')
        ->get()
        ->toArray();

        $ids = array_column($ratings, 'id_place');

        foreach ($json['elements'] as $key => $value) {
            $id = $json['elements'][$key]['id'];
            // array_search
            $pos = array_search($id, $ids);
            if($pos !== FALSE){
                if($ratings[$pos]['count'] > 0){
                    $json['elements'][$key]['tags']['rating'] = (int)$ratings[$pos]['sum'] / (int)$ratings[$pos]['count'];
                }
            }else{
                $json['elements'][$key]['tags']['rating'] = NULL;
            }
        }

        return response()->json($json);
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
                'rating' => round($rating, 2),
                'komentar' => $comments
            ]);
        }
        return response()->json(['isFound' => FALSE]);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'amenity' => 'required|in:restaurant,cafe'
        ]);

        if($validator->fails()){
            return response()->json(['success' => FALSE], 400);
        }

        Place::create([
            'id' => Uuid::uuid4(),
            'nama' => $request->nama,
            'lat' => $request->lat,
            'long' => $request->lon,
            'amenity' => $request->amenity
        ]);

        return redirect('/')->with('msg', 'Tempat berhasil ditambahkan.');
    }
}
