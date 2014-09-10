<?php
namespace GeoServices\Services;
use GeoServices\GeoException;
use GeoServices\GeoObject;

/**
 * Реализует freegeoip сервис
 *
 * @author kubrey
 */
class Freegeoip {
    private $method = 'freegeoip';
    private $url = 'http://freegeoip.net/json/';
    private $ip;

    /**
     * 
     * @param string $ip
     * @param array $options
     * @return \GeoServices\GeoObject
     * @throws GeoException
     */
    public function lookup($ip,$options = array()) {
        if(!filter_var($ip, FILTER_VALIDATE_IP)){
            throw new GeoException('Invalid IP address is set');
        }
        $this->ip = $ip;
        $url = $this->url . $ip;

        $curlOptions = array(
            CURLOPT_HEADER => false,
            CURLOPT_URL=>$url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/3.0.0.6",
            CURLOPT_TIMEOUT => 5
        );
        $ch = curl_init();
        if (!curl_setopt_array($ch, $curlOptions)) {
            throw new GeoException('Failed to set curl options');
        }
        $json = curl_exec($ch);
        $errors = curl_error($ch);
        if (!empty($errors)) {
            throw new GeoException('Curl error:' . $errors);
        }
        
        $data = json_decode($json);

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
        $geo->countryCode = (isset($obj->country_code)) ? strtolower($obj->country_code) : null;
        $geo->countryName = (isset($obj->country_name)) ? ($obj->country_name) : null;
        $geo->regionName = (isset($obj->region_name)) ? ($obj->region_name) : null;
        $geo->latitude = (isset($obj->latitude)) ? ($obj->latitude) : null;
        $geo->longitude = (isset($obj->longitude)) ? ($obj->longitude) : null;
        $geo->city = (isset($obj->city)) ? ($obj->city) : null;
        $geo->zip = (isset($obj->zipcode)) ? ($obj->zipcode) : null;
        $geo->method = $this->method;
        
        return $geo;
    }
}
