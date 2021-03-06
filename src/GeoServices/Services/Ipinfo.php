<?php

namespace GeoServices\Services;

use GeoServices\GeoException;
use GeoServices\GeoObject;
use GeoServices\Services\Service;

/**
 * Реализует Ipinfo
 *
 * @author kubrey
 */
class Ipinfo implements Service{

    private $method = 'ipinfo';
    private $url = 'http://ipinfo.io/';
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
            CURLOPT_TIMEOUT_MS => (isset($options['timeout']) ? $options['timeout'] : 2000)
        );
        $ch = curl_init();
        if (!curl_setopt_array($ch, $optionsCurl)) {
            throw new GeoException('Failed to set curl options');
        }
        $json = curl_exec($ch);
        $errors = curl_error($ch);
        if (!empty($errors)) {
            throw new GeoException('Curl error:' . $errors);
        }
        $data = json_decode($json);
        if (isset($data->loc) && !empty($data->loc) && strpos($data->loc, ',') !== false) {
            $coords = explode(',', $data->loc);
            if (is_array($coords) && count($coords) == 1) {
                $data->latitude = trim($coords[0]);
                $data->longitude = trim($coords[1]);
            }
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
        $geo->countryCode = (isset($obj->country)) ? strtolower($obj->country) : null;
        $geo->countryName = (isset($obj->country_name)) ? ($obj->country_name) : null;
        $geo->regionName = (isset($obj->region)) ? ($obj->region) : null;
        $geo->latitude = (isset($obj->latitude)) ? ($obj->latitude) : null;
        $geo->longitude = (isset($obj->longitude)) ? ($obj->longitude) : null;
        $geo->city = (isset($obj->city)) ? ($obj->city) : null;
        $geo->zip = (isset($obj->zipcode)) ? ($obj->zipcode) : null;
        $geo->isp = (isset($obj->org)) ? ($obj->org) : null;
        $geo->method = $this->method;

        return $geo;
    }

}
