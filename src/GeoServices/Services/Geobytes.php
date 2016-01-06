<?php

namespace GeoServices\Services;

use GeoServices\GeoException;
use GeoServices\GeoObject;

/**
 * Реализует сервис Geobytes
 * Cервис имеет ограничения  на количество вызовов в сутки
 *
 * @author kubrey
 */
class Geobytes {

    private $method = 'geobytes';
    private $url = 'http://www.geobytes.com/IpLocator.htm?GetLocation&template=php3.txt&IpAddress=';
    private $ip;

    /**
     * 
     * @param string $ip
     * @param array $options
     * @return \GeoServices\GeoObject
     * @throws GeoException
     */
    public function lookup($ip, $options = array()) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new GeoException('Invalid IP address is set');
        }
        $this->ip = $ip;
        $url = $this->url . $ip;
        $tags = get_meta_tags($url);
        $data = json_decode(json_encode($tags), false);
        if (isset($data->city) && $data->city == 'Limit Exceeded') {
            throw new GeoException('Limit of queries per day Exceeded ');
        }

        return $this->formalize($data);
    }

    /**
     * 
     * @param \stdClass $obj
     * @return \GeoServices\GeoObject
     */
    private function formalize($obj) {
        $geo = new GeoObject();
        $geo->ip = $this->ip;
        $geo->countryCode = (isset($obj->iso2)) ? strtolower($obj->iso2) : null;
        $geo->countryName = (isset($obj->country)) ? ($obj->country) : null;
        $geo->regionName = (isset($obj->region)) ? ($obj->region) : null;
        $geo->latitude = (isset($obj->latitude)) ? ($obj->latitude) : null;
        $geo->longitude = (isset($obj->longitude)) ? ($obj->longitude) : null;
        $geo->city = (isset($obj->city)) ? ($obj->city) : null;
        $geo->method = $this->method;

        return $geo;
    }

}
