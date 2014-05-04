<?php

namespace GeoServices\Services;

use GeoServices\GeoObject;
use GeoServices\GeoException;
use GeoIp2\Database\Reader;

/**
 * Description of Maxmind
 *
 * @author kubrey
 */
class Maxmind {

    private $method = 'maxmind';
    private $ip;

     /**
     * 
     * @param string $ip
     * @param array $options
     * @return \GeoServices\GeoObject
     * @throws GeoException
     */
    public function lookup($ip, $options = array()) {
        $this->ip = $ip;
        if (!isset($options[$this->method.'db']) || !is_file($options[$this->method.'db'])) {
            throw new GeoException('db file is invalid for ' . $this->method);
        }
        $geo = new Reader($options[$this->method.'db']);
        try {
            $data = $geo->city($ip);
            if (!$data) {
                throw new GeoException('Failed to get geoip data from ' . $this->method);
            }
            return $this->formalize($data);
        } catch (\GeoIp2\Exception\AddressNotFoundException $ex) {
            throw new GeoException($ex->getMessage());
        } catch (\GeoIp2\Exception\AuthenticationException $ex) {
             throw new GeoException($ex->getMessage());
        } catch (\Exception $ex) {
            throw new GeoException($ex->getMessage());
        }
        
    }

    /**
     * 
     * @param \GeoIp2\Model\City $obj
     * @return \GeoServices\Services\GeoObject
     */
    private function formalize($obj) {
        $geo = new GeoObject();
        $geo->ip = $this->ip;
        $geo->countryCode = (isset($obj->country->isoCode)) ? strtolower($obj->country->isoCode) : null;
        $geo->countryName = (isset($obj->country->name)) ? strtolower($obj->country->name) : null;
        $geo->latitude = (isset($obj->location->latitude)) ? ($obj->location->latitude) : null;
        $geo->longitude = (isset($obj->location->longitude)) ? ($obj->location->longitude) : null;
        $geo->city = (isset($obj->city->name)) ? ($obj->city->name) : null;
//        echo $geo->countryCode;
        return $geo;
    }

}
