<?php

namespace GeoServices\Services;

use GeoServices\GeoException;
use GeoServices\GeoObject;
use GeoServices\Services\Service;

/**
 * ip-api.com
 *
 * @author kubrey
 */
class IpApi implements Service{

    private $method = 'ipapi';
    private $url = 'http://ip-api.com/json/';
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

        $optionsCurl = array(
            CURLOPT_HEADER => false,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5
        );

        $ch = curl_init();
        if (!curl_setopt_array($ch, $optionsCurl)) {
            throw new GeoException('Failed to set curl options');
        }
        $json = curl_exec($ch);
        $errors = curl_error($ch);

        $info = curl_getinfo($ch);
        if ($errors) {
            throw new GeoException('Curl error:' . $errors);
        }
        if (!isset($info['content_type']) || strtolower($info['content_type']) !== 'application/json; charset=utf-8') {
              throw new GeoException('Content Type is not valid:' . $info['content_type']);
        }
        $data = json_decode($json);

        return $this->formalize($data);
    }

    /**
     * 
     * @param stdClass $obj
     * @return GeoObject
     */
    private function formalize($obj) {
        $geo = new GeoObject();
        $geo->ip = $this->ip;
        $geo->countryCode = (isset($obj->countryCode)) ? strtolower($obj->countryCode) : null;
        $geo->countryName = (isset($obj->country)) ? ($obj->country) : null;
        $geo->regionName = (isset($obj->regionName)) ? ($obj->regionName) : null;
        $geo->latitude = (isset($obj->lat)) ? ($obj->lat) : null;
//        $geo->timezone = (isset($obj->latitude)) ? ($obj->latitude) : null;
        $geo->longitude = (isset($obj->lon)) ? ($obj->lon) : null;
        $geo->city = (isset($obj->city)) ? ($obj->city) : null;
        $geo->zip = (isset($obj->zip)) ? ($obj->zip) : null;
        $geo->isp = (isset($obj->as)) ? ($obj->as) : null;
        $geo->method = $this->method;

        return $geo;
    }

}
