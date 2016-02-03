<?php

namespace GeoServices\Services;

use GeoServices\GeoObject;
use GeoServices\GeoException;
use MaxmindLegacy\GeoIPCity;
use MaxmindLegacy\GeoIP;
use GeoServices\Services\Service;

/**
 * Реализует Maxmind Legacy(по .dat базе)
 *
 * @author kubrey <kubrey@gmail.com>
 */
class MaxmindOld implements Service{

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
        if(!filter_var($ip, FILTER_VALIDATE_IP)){
            throw new GeoException('Invalid IP address is set');
        }
        $this->ip = $ip;
        $parts = explode('.',$options[$this->method . 'db']);
        $ext = (is_array($parts) ? end($parts) : '');
        if (!isset($options[$this->method . 'db']) || !is_file($options[$this->method . 'db']) || !is_readable($options[$this->method . 'db']) || strtolower($ext)!='dat') {
            throw new GeoException('db file is invalid for ' . $this->method);
        }

        $r = new GeoIP();
        try {
            $g = $r->geoip_open($options[$this->method . 'db'], GeoIP::GEOIP_STANDARD);
            $c = new GeoIPCity($g);
            $object = $c->GeoIP_record_by_addr($ip);
            $r->geoip_close();
        } catch (\Exception $ex) {
            throw new GeoException($ex->getMessage());
        }

        if (isset($options[$this->method . 'isp']) && is_file($options[$this->method . 'isp']) && is_object($object)) {
            $risp = new GeoIP();
            try {
                $gisp = $risp->geoip_open($options[$this->method . 'isp'], GeoIP::GEOIP_ISP_EDITION);
                $cisp = new GeoIPCity($gisp);
                $isp = $cisp->GeoIP_record_by_addr($ip);

                $object->isp = ((is_object($isp) && isset($isp->region)) ? $isp->region : null);

                $risp->geoip_close();
            } catch (Exception $ex) {
                throw new GeoException($ex->getMessage());
            }
        } elseif(is_object($object)) {
            $object->isp = null;
        }

//        $gi = geoip_open($options[$this->method . 'db'], GEOIP_STANDARD);
//        $object = GeoIP_record_by_addr($gi, $this->ip);
//        if (isset($options[$this->method . 'isp']) && is_file($options[$this->method . 'isp'])) {
//            $gisp = geoip_open($options[$this->method . 'isp'], GEOIP_ISP_EDITION);
//            $isp = $cis->GeoIP_record_by_addr($gisp, $ip);
//            $object->isp = $isp->region;
//            geoip_close($gisp);
//        } else {
//            $object->isp = null;
//        }
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

//        geoip_close($gi);
        //

        return $this->formalize($obj);
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
