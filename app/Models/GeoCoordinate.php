<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoCoordinate extends Model
{
    use HasFactory;
    protected $table = 'geocoordinates';
    protected $fillable = ['id','longitude','latitude','address','city_id'];
}
