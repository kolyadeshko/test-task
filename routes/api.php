<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// получение и сохранение адресса по координатам
Route::post('get-my-address',
    [
        App\Http\Controllers\Api\AddressController::class,
        'getMyAddress'
    ]
);
