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
 * Класс поиска гео-данных по IP-адресу
 * @see https://bitbucket.org/kubrey/geoservice
 * @property  boolean|int $maxmind False - если не использовать, int - прио
 * @property  boolean|int $ipgeobase False - если не использовать, int - прио
 * @property  boolean|int $freegeoip False - если не использовать, int - прио
 * @property  boolean|int $ipinfo False - если не использовать, int - прио
 * @property  boolean|int $geobytes False - если не использовать, int - прио
 * @property  boolean|int $telize False - если не использовать, int - прио
 * @property  boolean|int $telize False - если не использовать, int - прио
 * 
 * @property boolean $isCityRequired обязательно ли найти город
 * @property boolean $isCountryCodeRequired обязательно ли найти код страны
 * @property boolean $isCountryNameRequired обязательно ли найти название старны
 * @property boolean $isRegionNameRequired обязательно ли найти регион
 * @property boolean $isLatitudeRequired обязательно ли найти долготу
 * @property boolean $isLongitudeRequired обязательно ли найти широту
 * @property boolean $isZipRequired обязательно ли найти zip
 * @property boolean $isIspRequired обязательно ли найти провайдера
 * @property $accumulativeGeo Объект, накапливающий собранные гео-параметры
 * @author kubrey <kubrey@gmail.com>
 * @todo Добавить поиск по ipv6
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
    private $maxmindIspDb;
    public $isCityRequired = true;
    public $isCountryCodeRequired = true;
    public $isCountryNameRequired = true;
    public $isLatitudeRequired = false;
    public $isLongitudeRequired = false;
    public $isRegionNameRequired = false;
    public $isIspRequired = false;
    public $isZipRequired = false;
    private $configs = array(
        'Maxmind' => array('type' => 'standalone'),
        'Ipgeobaseru' => array('type' => 'standalone', 'cc' => array('ua', 'ru')),
        'Freegeoip' => array('type' => 'service'),
        'Ipinfo' => array('type' => 'service'),
        'Telize' => array('type' => 'service'),
        'Geobytes' => array('type' => 'service'),
        'Maxmindold' => array('type' => 'standalone')
    );
    private $lastResponce = null;
    private $errors = array();
    private $accumulativeGeo;

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
     * Указать полный путь к dat файлу с базой ISP от maxmind
     * @param string $file
     * @return \GeoServices\GeoService
     * @throws GeoException
     */
    public function setMaxmindISPDb($file){
        if (!is_file($file)) {
            throw new GeoException('Wrong maxmind isp db path');
        }
        $this->maxmindIspDb = $file;
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

        $properties = GeoObject::getProperties();
        $nonempty = false;
        foreach ($properties as $pr) {
            if ($this->{'is' . ucfirst($pr) . 'Required'} === true) {
                $nonempty = true;
                break;
            }
        }
        if (!$nonempty) {
            throw new GeoException('All geo object properties are disabled, nothing to look up');
        }

        $options = array();
        $options['maxminddb'] = $this->maxmindDb;
        $options['maxmindolddb'] = $this->maxmindOldDb;
        $options['maxmindoldisp'] = $this->maxmindIspDb;
        asort($methods);
        foreach ($methods as $m => $mode) {
            if (!$mode) {
                continue;
            }

            try {
                if (array_key_exists('cc', $this->configs[$m])) {
                    //метод работающий под конкретные страны
                    if (!empty($this->lastResponce) && !empty($this->lastResponce->countryCode) && !in_array($this->lastResponce->countryCode, $this->configs[$m]['cc'])) {
                        continue;
                    }
                }
                //$geo = new $m(); не срабатывает, печаль
                switch ($m) {
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
                $this->accumulate();
                $complete = true;
                foreach ($properties as $pr) {
                    if ($this->{'is' . ucfirst($pr) . 'Required'} === true && empty($this->accumulativeGeo->{$pr})) {
                        $complete = false;
                        break;
                    }
                }
                if (!$complete) {
                    $this->errors[$m] = 'Not all required properties found';
                    continue;
                }
//                return $res;
                return $this->accumulativeGeo;
            } catch (\GeoServices\GeoException $ex) {
                $this->errors[$m] = $ex->getMessage();
                continue;
            }
        }
        throw new GeoException('no data, ' . print_r($this->errors, true));
    }

    /**
     * Возвращает массив вызовов, предшествующих удачному
     * @return array
     */
    public function getCallStack() {
        return $this->errors;
    }

    /**
     * Аккумуляция собранных данных
     * @return GeoService
     */
    protected function accumulate() {
        foreach ($this->lastResponce as $propName => $val) {
            if(!isset($this->accumulativeGeo->{$propName}) || empty($this->accumulativeGeo->{$propName})){
                $this->accumulativeGeo->{$propName} = $val;
            }
            if($propName == 'method'){
                $this->accumulativeGeo->method = $val;
            }
        }
        return $this;
    }

}
