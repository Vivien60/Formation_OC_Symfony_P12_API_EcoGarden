<?php

namespace App\Mapper;

use App\DTO\Meteo;

class OpenWeatherMapToMeteoMapper
{
    public function map(array $data): Meteo
    {
        $meteo = new Meteo();
        $meteo->city = $data['name'];
        $meteo->longitude = $data['coord']['lon'];
        $meteo->latitude = $data['coord']['lat'];
        $meteo->windSpeed = $data['wind']['speed'];
        $meteo->windDeg = $data['wind']['deg'];
        $meteo->temperature = $data['main']['temp'];
        $meteo->humidity = $data['main']['humidity'];
        $meteo->pressure = $data['main']['pressure'];

        return $meteo;
    }
}