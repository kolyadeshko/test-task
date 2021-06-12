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
// получение всех адрессов которые записаны в бд или по региону
Route::get('get-addresses/{regionId?}',
    [
        App\Http\Controllers\Api\AddressController::class,
        'getAddresses'
    ]
) -> whereNumber('regionId');
