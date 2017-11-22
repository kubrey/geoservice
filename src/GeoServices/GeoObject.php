<?php

namespace GeoServices;

/**
 * @property string $ip
 * @property string $countryName
 * @property string $city
 * @property string $latitude
 * @property string $longitude
 * @property string $zip
 * @property string $regionName
 * @property string $countryCode
 * @property string $isp
 * @property string $method
 *
 * @author kubrey
 */
class GeoObject
{

    public $ip;
    public $countryName;
    public $city;
    public $latitude;
    public $longitude;
    public $zip;
    public $regionName;
    public $countryCode; //2letter
    public $isp;
    public $method;

    public function __construct() {

    }

    /**
     * Возвращает массив свойств гео-объекта
     * @return array
     */
    public static function getProperties() {
        return array('countryName', 'city', 'latitude', 'longitude', 'zip', 'regionName', 'countryCode', 'isp');
    }

}
