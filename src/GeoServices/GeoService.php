<?php

namespace GeoServices;

use GeoServices\Services\Freegeoip;
use GeoServices\Services\Geobytes;
use GeoServices\Services\Ipgeobaseru;
use GeoServices\Services\Ipinfo;
use GeoServices\Services\Maxmind;
use GeoServices\Services\Telize;
use GeoServices\Services\MaxmindOld;
use GeoServices\GeoException;

/**
 * @property  boolean|int $maxmind False - если не использовать, int - прио
 * @property  boolean|int $ipgeobase
 * @property  boolean|int $freegeoip
 * @property  boolean|int $ipinfo 
 * @property  boolean|int $geobytes
 * @property  boolean|int $telize
 * @property  boolean $searching4City Поиск  города(true) или  достаточно страны(false)
 * @author kubrey <kubrey@gmail.com>
 */
class GeoService {

    public $maxmind = 1;
    public $ipgeobase = 2;
    public $freegioip = 3;
    public $ipinfo = 5;
    public $geobytes = 6;
    public $telize = 4;
    public $maxmindold = 1;
    private $maxmindDb;
    private $maxmindOldDb;
    public $searching4City = true;
    private $configs = array(
        'Maxmind' => array('type' => 'standalone'),
        'Ipgeobaseru' => array('type' => 'standalone', 'cc' => array('ua', 'ru')),
        'Freegeoip' => array('type' => 'service'),
        'Ipinfo' => array('type' => 'service'),
        'Telize' => array('type' => 'service'),
        'Geobytes' => array('type' => 'service'),
        'Maxmindold' => array('type'=>'standalone')
    );
    private $lastResponce = null;
    private $errors = array();

    public function __construct() {
        
    }

    /**
     * Указать путь к базе maxmind *.mmdb
     * @param string $file Полный путь к базе *.mmdb
     * @throws GeoException
     * @return \GeoServices\GeoService
     */
    public function setMaxmindDb($file) {
        if (!is_file($file)) {
            throw new GeoException('Wrong maxmind db path');
        }
        $this->maxmindDb = $file;
        return $this;
    }
    
    /**
     * Указать путь к базе maxmind *.dat
     * @param string $file Полный путь к базе *.dat
     * @throws GeoException
     * @return \GeoServices\GeoService
     */
    public function setMaxmindOldDb($file) {
        if (!is_file($file)) {
            throw new GeoException('Wrong maxmindold db path');
        }
        $this->maxmindOldDb = $file;
        return $this;
    }

    /**
     * Получить geo-данные
     * @param string $ip
     * @return \GeoServices\GeoObject
     * @throws GeoException
     */
    public function lookup($ip) {
        $methods = array();
        foreach ($this->configs as $method => $prop) {
            if (isset($this->{strtolower($method)}) && $this->{strtolower($method)}) {
                $methods[$method] = $this->{strtolower($method)};
            }
        }
        
        $options = array();
        $options['maxminddb'] = $this->maxmindDb;
        $options['maxmindolddb'] = $this->maxmindOldDb;
        asort($methods);
        foreach ($methods as $m=>$mode) {
            if(!$mode){
                continue;
            }
            
            try {
                if (array_key_exists('cc', $this->configs[$m])) {
                    //метод работающий под конкретные страны
                    if (!empty($this->lastResponce) && !empty($this->lastResponce->countryCode) && $this->searching4City && !in_array($this->lastResponce->countryCode, $this->configs[$m]['cc'])) {
                        continue;
                    }
                }
                //$geo = new $m(); не срабатывает, печаль
                switch($m){
                    case 'Maxmind':
                        $geo = new Maxmind();
                        break;
                    case 'Ipgeobaseru':
                        $geo = new Ipgeobaseru();
                        break;
                     case 'Telize':
                        $geo = new Telize();
                        break;
                     case 'Freegeoip':
                        $geo = new Freegeoip();
                        break;
                     case 'Geobytes':
                        $geo = new Geobytes();
                        break;
                     case 'Ipinfo':
                        $geo = new Ipinfo();
                        break;
                    case 'Maxmindold':
                        $geo = new MaxmindOld();
                        break;
                }
                $res = $geo->lookup($ip, $options);
                $this->lastResponce = $res;
                if (!empty($res->city)) {
                    return $res;
                } elseif (!$this->searching4City && !empty($res->countryCode)) {
                    return $res;
                }
            } catch (\GeoServices\GeoException $ex) {
                $this->errors[$m] = $ex->getMessage();
                continue;
            }
        }
        throw new GeoException('no data, '.print_r($this->errors, true));
    }
    
    /**
     * 
     * @return array
     */
    public function getCallStack(){
        return $this->errors;
    }

}
