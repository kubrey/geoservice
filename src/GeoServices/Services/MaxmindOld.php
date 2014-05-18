<?php

namespace GeoServices\Services;

use GeoServices\GeoObject;
use GeoServices\GeoException;

/**
 * Реализует Maxmind Legacy(по .dat базе)
 *
 * @author kubrey <kubrey@gmail.com>
 */
class MaxmindOld {

    private $method = 'maxmindold';
    private $ip;

    /**
     * 
     * @param string $ip
     * @param array $options
     * @return GeoObject
     * @throws GeoException
     */
    public function lookup($ip, $options = array()) {
        $this->ip = $ip;
        if (!isset($options[$this->method . 'db']) || !is_file($options[$this->method . 'db'])) {
            throw new GeoException('db file is invalid for ' . $this->method);
        }
        $gi = geoip_open($options[$this->method . 'db'], GEOIP_STANDARD);
        $object = GeoIP_record_by_addr($gi, $this->ip);
        if (isset($options[$this->method . 'isp']) && is_file($options[$this->method . 'isp'])) {
            $gisp = geoip_open($options[$this->method . 'isp'], GEOIP_ISP_EDITION);
            $isp = GeoIP_record_by_addr($gisp, $ip);
            $object->isp = $isp->region;
            geoip_close($gisp);
        }

        //
        if (is_object($object)) {
            foreach ($object as $k => $oitem) {
                if (is_string($oitem)) {
                    $object->{$k} = iconv('ISO-8859-1', 'utf8', $oitem);
                }
            }
        }

        if (is_object($object)) {
            $obj = new \stdClass();
            foreach ($object as $key => $val) {
                $obj->{$key} = $val;
            }
        } else {
            throw new GeoException("Failed to get geo-data by " . $this->method);
        }

        geoip_close($gi);
        //

        return $this->formalize($obj);
    }

    private function ispLookup() {
        
    }

    /**
     * 
     * @param \stdClass $obj
     * @return \GeoServices\Services\GeoObject
     */
    private function formalize($obj) {
        $geo = new GeoObject();
        $geo->ip = $this->ip;
        $geo->countryCode = (isset($obj->country_code)) ? strtolower($obj->country_code) : null;
        $geo->countryName = (isset($obj->country_name)) ? ($obj->country_name) : null;
        $geo->latitude = (isset($obj->latitude)) ? ($obj->latitude) : null;
        $geo->longitude = (isset($obj->longitude)) ? ($obj->longitude) : null;
        $geo->city = (isset($obj->city)) ? ($obj->city) : null;
        $geo->method = $this->method;
        $geo->isp = $obj->isp;
        return $geo;
    }

}
