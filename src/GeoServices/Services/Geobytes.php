<?php

namespace GeoServices\Services;

use GeoServices\GeoException;
use GeoServices\GeoObject;
use GeoServices\Services\Service;

/**
 * Реализует сервис Geobytes
 * Cервис имеет ограничения  на количество вызовов в сутки
 *
 * @author kubrey
 */
class Geobytes implements Service{

    private $method = 'geobytes';
    private $url = 'http://getcitydetails.geobytes.com/GetCityDetails?fqcn=';
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
            if (strpos($errors, 'Operation timed out') !== false) {
                $ip = php_sapi_name() == 'cli' ? 'x.x.x.x(cli)' : filter_input(INPUT_SERVER, 'REMOTE_ADDR');
                //very likely service has banned requester's ip
                //needs to be unban manually
                /**
                 * @link http://ip-api.com/docs/unban submit your ip address
                 */
                $errors.="; seems that your ip address(" . $ip . ") was banned by " . $this->method . ". Use this link http://ip-api.com/docs/unban to handle that";
            }
            throw new GeoException('Curl error:' . $errors);
        }
//        if (!isset($info['content_type']) || strtolower($info['content_type']) !== 'application/json; charset=utf-8') {
//            throw new GeoException('Content Type is not valid:' . $info['content_type']);
//        }
        $data = json_decode($json);
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
        $geo->countryCode = (isset($obj->geobytesinternet)) ? strtolower($obj->geobytesinternet) : null;
        $geo->countryName = (isset($obj->geobytescountry)) ? ($obj->geobytescountry) : null;
        $geo->regionName = (isset($obj->geobytesregion)) ? ($obj->geobytesregion) : null;
        $geo->latitude = (isset($obj->geobyteslatitude)) ? ($obj->geobyteslatitude) : null;
        $geo->longitude = (isset($obj->geobyteslongitude)) ? ($obj->geobyteslongitude) : null;
        $geo->city = (isset($obj->geobytescity)) ? ($obj->geobytescity) : null;
        $geo->method = $this->method;

        return $geo;
    }

}
