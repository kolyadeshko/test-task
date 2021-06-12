<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeocoordinatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geocoordinates', function (Blueprint $table) {
            $table->id();
            $table->string('longitude',20);
            $table->string('latitude',20);
            $table->text('address');
            $table->unsignedBigInteger('city_id') -> nullable();
            $table->timestamps();
        });
        Schema::table('geocoordinates',function (Blueprint $table){
            $table
                -> foreign('city_id')
                -> references('id')
                -> on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geocoordinates');
    }
}
