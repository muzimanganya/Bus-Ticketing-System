<?php
namespace app\utils;

class CountryUtils
{
    public static $iataMap = [
        'TZA' => 'Tanzania',
        'UGA' => 'Uganda',
        'RWA' => 'Rwanda',
        'COD' => 'Congo (Democratic Republic)',
        'KEN' => 'Kenya',
        'BDI' => 'Burundi',
    ];

    public static function getIATACountries(): array
    {
        return self::$iataMap;
    }

    public static function getIATACode(string $name): string
    {
        foreach(self::$iataMap as $code => $cname){
            if(strtolower($name) == strtolower($cname)){
                return $code;
            }
        }
        return 'UNKNOWN';
    }
}   