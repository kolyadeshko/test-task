<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\GeoCoordinate;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    // получение всех адрессов которые записаны в бд или по региону
    public function getAddresses($regionId = false)
    {
        // если пришел id региона
        if ($regionId)
        {
            $region = Region::query() -> find($regionId);
            // если такой регион существует отдаем адреса, если нет, ответ что его нет
            return $region ? $region -> addresses : 'Такого региона нет';
        } else {
            return GeoCoordinate::all();
        }
    }
    // получение и сохранение адресса по координатам
    public function getMyAddress(Request $request)
    {
        // получаем координаты
        $coordinates = $request -> only(['longitude','latitude']);
        // валидируем координаты
        $validate = $this -> validateCoordinates($coordinates);
        if (!$validate['status']) return $validate['error'];
        // если данные корректны, запрашиваем
        $addressData = $this -> getAddressDataByCoordinates($coordinates);
        // результирующий ответ составленный из данных адреса
        $resultAddress = '';
        // проходим по всем компонентам адреса и формируем ответ
        foreach ($addressData as $addressItem)
        {
            $resultAddress .= implode(',',$addressItem -> types) . " : " . $addressItem -> long_name . "   ";
        }
        // сохраняем данные в базу данных
        $this -> saveAddressData($coordinates,$resultAddress,$addressData);
        return $resultAddress;
    }
    // обработка и сохранение данных
    private function saveAddressData($coordinates,$resultAddress,$addressData)
    {
        $region = $city = false;
        foreach ($addressData as $addressItem)
        {
            // ищем в компонентам адресса название страны и города
            if (in_array('country',$addressItem -> types))
            {
                $region = $addressItem -> long_name;
            }
            if (in_array('locality',$addressItem -> types))
            {
                $city = $addressItem -> long_name;
            }
        }
        if ($region)
        { // если найден регион, проверяем есть ли он в бд, и если нету - сохраняем
            $region = Region::query() -> firstOrCreate(['name' => $region]);
            // то же самое с городом
            if ($city){
                $city = City::query() -> firstOrCreate(['name' => $city,'region_id' =>$region -> id]);
            }
        }
        // geoData - массив с помощью которого сохраняют данные
        // в таблицу geocoordinates
        $geoData = array_merge(
            $coordinates,
            [
                'address' => $resultAddress,
            ]
        );
        // если найден город, то добавляем ссылку на него
        if ($city) $geoData = array_merge($geoData,['city_id' => $city -> id]);
        // записываем в бд
        GeoCoordinate::query() -> create($geoData);
    }

    // проверка координат
    private function validateCoordinates($coordinates)
    {
        // проверяем пришли ли нужные данные
        if (!isset($coordinates['longitude']) || !isset($coordinates['latitude'])){
            return [
                'status' => false,
                'error' => 'Не указана одна из координат'
            ];
        }
        // далее валидируем сам формат координат
        $validator = Validator::make(
            $coordinates,
            [
                'longitude' => ['regex:/^\-?\d+(\.\d+)?$/i'],
                'latitude' => ['regex:/^\-?\d+(\.\d+)?$/i']
            ],
            [
                'longitude.regex' => 'Не корректный формат долготы',
                'latitude.regex' => 'Не корректный формат широты'
            ]
        );
        // если валидация не пройдена, возвращаем ошибку
        if ($validator -> fails()) return [
            'status' => false,
            'error' => $validator -> errors() -> first()
        ];
        // если валидация пройдена, возвращаем true
        return ['status' => true];
    }
    // получение данных об адресе
    private function getAddressDataByCoordinates($coordinates)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        // составляем строку запроса
        $query = '?' . 'latlng=' . implode(',',$coordinates) . '&key=' . env('GEOCODING_ACCESS_KEY_ID');
        $curl = curl_init($url . $query);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        // достаем из ответа только нужные данные
        $response = json_decode(curl_exec($curl)) -> results[0] -> address_components;
        curl_close($curl);
        return $response;
    }
}
