<?php

namespace GeoServices\Services;

use GeoServices\GeoException;
use GeoServices\GeoObject;
use GeoServices\Services\Service;

/**
 * ip-api.com
 * Usage limit: 150 requests per minute
 * That's whay this service may be not useful as a primary geolocation service
 * Overloading this requests leads to IP ban for whole site
 * Unban url http://ip-api.com/docs/unban(captcha requires)
 *
 * @author kubrey
 */
class IpApi implements Service {

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
            CURLOPT_TIMEOUT_MS => (isset($options['timeout']) ? $options['timeout'] : 2000)
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
        if (!isset($info['content_type']) || strtolower($info['content_type']) !== 'application/json; charset=utf-8') {
            throw new GeoException('Content Type is not valid:' . $info['content_type']);
        }
        $data = json_decode($json);
        if (!isset($data->status) || $data->status != "success") {
            $errmsg = (isset($data->message) ? $data->message : "");
            throw new GeoException("Failed to get ip data: " . $errmsg);
        }

        return $this->formalize($data);
    }

    /**
     * 
     * @param \stdClass $obj
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
