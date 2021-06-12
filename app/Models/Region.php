<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;
    protected $table = 'regions';
    protected $fillable = ['id','name'];

    public function addresses()
    {
        return $this -> hasManyThrough(
            GeoCoordinate::class,
            City::class,
            'region_id',
            'city_id'
        );
    }

}
