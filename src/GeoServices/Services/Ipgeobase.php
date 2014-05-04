<?php
namespace GeoServices\Services;
use Ipgeobase\IpGeobase;
use GeoServices\GeoException;
use GeoServices\GeoObject;

/**
 * Description of Ipgeobase
 *
 * @author kubrey
 */
class Ipgeobase {
    private $method = 'ipgeobase';
    private $ip;
    
     /**
     * 
     * @param string $ip
     * @param array $options
     * @return \GeoServices\GeoObject
     * @throws GeoException
     */
    public function lookup($ip,$options = array()) {
        $this->ip = $ip;
        $geo = new IpGeobase();
        try {
            $data = $geo->lookup($ip); 
            if(!$data){
                throw new GeoException('Failed to get geoip data from '.$this->method);
            }
        } catch (\Exception $ex) {
            throw new GeoException($ex->getMessage());
        }
        return $this->formalize($data);
    }
    
    /**
     * 
     * @param \stdClass $obj
     * @return \GeoServices\Services\GeoObject
     */
    private function formalize($obj){
        $geo = new GeoObject();
        $geo->ip = $this->ip;
        $geo->countryCode = (isset($obj->cc)) ? strtolower($obj->cc) : null;
        $geo->regionName = (isset($obj->region)) ? ($obj->region) : null;
        $geo->latitude = (isset($obj->lat)) ? ($obj->lat) : null;
        $geo->longitude = (isset($obj->lng)) ? ($obj->lng) : null;
        $geo->city = (isset($obj->city)) ? ($obj->city) : null;
        
        return $geo;
    }
}
