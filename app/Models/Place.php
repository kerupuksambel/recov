<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $table = 'places';
    protected $fillable = [
        'id',
        'nama',
        'lat',
        'long',
        'amenity'
    ];

    protected $casts = ['id' => 'string'];
}
