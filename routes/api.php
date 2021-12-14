<?php

use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('place/all/', [PlaceController::class, 'index']);
Route::get('place/detail/{id_place}', [PlaceController::class, 'detail']);
Route::post('place/add/', [PlaceController::class, 'add']);

Route::post('review/submit/{id_place}', [ReviewController::class, 'add']);
