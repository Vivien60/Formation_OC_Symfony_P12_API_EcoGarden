<?php

namespace App\DTO;

class Meteo
{
    public string $city;
    public float $longitude;
    public float $latitude;
    public float $windSpeed;
    public float $temperature;
    public float $humidity;
    public float $pressure;
    public float $windDeg; //Wind direction

}