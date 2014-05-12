<?php

namespace GeoServices;

/**
 * @property-write string $ip
 * @property-write string $countryName
 * @property-write string $city
 * @property-write string $latitude
 * @property-write string $longitude
 * @property-write string $zip
 * @property-write string $regionName
 * @property-write string $countryCode
 * @property-write string $isp
 * @property-write string $method
 *
 * @author kubrey
 */
class GeoObject {
    
    public $ip;
    public $countryName;
    public $city;
    public $latitude;
    public $longitude;
    public $zip;
    public $regionName;
    public $coutntryCode; //2letter
    public $isp;
    public $method;
    
    
    public function __construct() {
        
    }
    
    /**
     * Возвращает массив свойств гео-объекта
     * @return array
     */
    public static function getProperties(){
        return array('countryName','city','latitude','longitude','zip','regionName','countryCode','isp');
    }
}
