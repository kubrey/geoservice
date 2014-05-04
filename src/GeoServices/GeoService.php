<?php

namespace GeoServices;

use GeoIp2\Database\Reader;
use Ipgeobase\IpGeobase;

/**
 * @property  boolean|int $maxmind False - если неиспользовать, int - прио
 * @property  boolean|int $ipgeobase
 * @property  boolean|int $freegeoip
 * @property  boolean|int $ipinfo 
 * @property  boolean|int $geobytes
 * @property  boolean|int $telize
 * @property  boolean $searching4City Поиск  города(true) или  достаточно страны(false)
 * @author kubrey
 */
class GeoService {

    public $maxmind = 1;
    public $ipgeobase = 2;
    public $freegioip = 3;
    public $ipinfo = 5;
    public $geobytes = 6;
    public $telize = 4;
    private $maxmindDb;
    public $searching4City = true;
    private $configs = array(
        'maxmind' => array('type' => 'standalone'),
        'ipgeobase' => array('type' => 'standalone', 'cc' => array('ua', 'ru')),
        'freegeoip' => array('type' => 'service'),
        'ipinfo' => array('type' => 'service'),
        'telize' => array('type' => 'service'),
        'geobytes' => array('type' => 'service')
    );

    public function __construct() {
        
    }

    /**
     * 
     * @param string $file Полный путь к базе *.mmdb
     * @throws Exception
     * @return \GeoServices\GeoService
     */
    public function setMaxminDb($file) {
        if (!is_file($file)) {
            throw new Exception('Wrong maxmind db path');
        }
        $this->maxmindDb = $file;
        return $this;
    }

    /**
     * 
     * @param string $ip
     */
    public function lookup($ip) {
        $methods = array(); 
        foreach ($this->configs as $method => $prop) {
            if(isset($this->{$method}) && $this->{$method}){
                $methods[$method] = $this->{$method};
            }
        }
        
        asort($methods);
        foreach ($methods as $m) {
            
        }
    }

}
