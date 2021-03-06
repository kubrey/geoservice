<?php

namespace GeoServices;

use GeoServices\GeoException;
use GeoServices\Services\Service;

/**
 * Класс поиска гео-данных по IP-адресу
 * @see https://github.com/kubrey/geoservice
 * @property  boolean|int $maxmind False - если не использовать, int - прио
 * @property  boolean|int $ipgeobase False - если не использовать, int - прио
 * @property  boolean|int $freegeoip False - если не использовать, int - прио
 * @property  boolean|int $ipinfo False - если не использовать, int - прио
 * @property  boolean|int $geobytes False - если не использовать, int - прио
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
 *
 * @property GeoObject|bool $lastResponse
 * @author kubrey <kubrey.work@gmail.com>
 * @todo Добавить поиск по ipv6
 */
class GeoService
{

    public $maxmind = 1;
    public $ipgeobase = 2;
    public $freegioip = 3;
    public $ipinfo = 5;
    public $geobytes = 6;
    public $ipapi = 6;
    public $telize = false;
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
    //
    private $configs = array(
        'Maxmind' => array('type' => 'standalone'),
        'Ipgeobaseru' => array('type' => 'standalone', 'cc' => array('ua', 'ru')),
        'Freegeoip' => array('type' => 'service'),
        'Ipinfo' => array('type' => 'service'),
        'IpApi' => array('type' => 'service'),
        'Telize' => array('type' => 'service'),
        'Geobytes' => array('type' => 'service'),
        'MaxmindOld' => array('type' => 'standalone')
    );
    private $lastResponse;
    private $errors = array();
    private $accumulativeGeo;

    protected $serviceTimeout = 0;
    protected $globalTimeout = 0;

    public function __construct() {
        $this->accumulativeGeo = new GeoObject;
    }

    /**
     * @param int $timeMs timeout for curl in ms
     * @return $this
     */
    public function setRequestTimeout($timeMs) {
        if (!is_numeric($timeMs) || $timeMs <= 0) {
            return $this;
        }
        $this->serviceTimeout = (int)$timeMs;
        return $this;
    }

    /**
     * If data hs not been found till specified time, further requests are ot executed and success:false in returned
     * @param int $timeMs timeout for whole lookup in ms
     * @return $this
     */
    public function setGlobalTimeout($timeMs) {
        if (!is_numeric($timeMs) || $timeMs <= 0) {
            return $this;
        }
        $this->globalTimeout = (int)$timeMs;
        return $this;
    }

    /**
     * Указать путь к базе maxmind *.mmdb
     * @param string $file Полный путь к базе *.mmdb
     * @return \GeoServices\GeoService
     */
    public function setMaxmindDb($file) {
        if (!is_file($file)) {
            $this->errors[] = "Wrong maxmind db path";
            $this->maxmind = false;
//            throw new GeoException('Wrong maxmind db path');
        } else {
            $this->maxmindDb = $file;
        }
        return $this;
    }

    /**
     * Указать полный путь к dat файлу с базой ISP от maxmind
     * @param string $file
     * @return \GeoServices\GeoService
     */
    public function setMaxmindISPDb($file) {
        if (!is_file($file)) {
            $this->errors[] = 'Wrong maxmind isp db path';
        } else {
            $this->maxmindIspDb = $file;
        }
        return $this;
    }

    /**
     * Указать путь к базе maxmind *.dat
     * @param string $file Полный путь к базе *.dat
     * @return \GeoServices\GeoService
     */
    public function setMaxmindOldDb($file) {
        if (!is_file($file)) {
            $this->errors[] = 'Wrong maxmind old db path';
            $this->maxmindold = false;
        } else {
            $this->maxmindOldDb = $file;
        }
        return $this;
    }

    /**
     * Получить geo-данные
     * @param string $ip
     * @return \GeoServices\GeoObject
     * @throws GeoException
     */
    public function lookup($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new GeoException('Invalid IP address is set');
        }
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
        $options['timeout'] = $this->serviceTimeout ? $this->serviceTimeout : 2000;
        $options['maxminddb'] = $this->maxmindDb;
        $options['maxmindolddb'] = $this->maxmindOldDb;
        $options['maxmindoldisp'] = $this->maxmindIspDb;
        asort($methods);
        $start = microtime(true) * 1000;
        foreach ($methods as $m => $mode) {
            if (!$mode) {
                continue;
            }
            $current = microtime(true) * 1000;
            if ($this->globalTimeout && (($current - $start) > $this->globalTimeout)) {
                $this->errors[$m] = "Timeout(" . $this->globalTimeout . " ms) has been exceeded";
                break;
            }

            try {
                if (array_key_exists('cc', $this->configs[$m])) {
                    //метод работающий под конкретные страны
                    if ($this->lastResponse && $this->lastResponse->countryCode && !in_array($this->lastResponse->countryCode, $this->configs[$m]['cc'])) {
                        continue;
                    }
                }
                $class = "GeoServices\Services\\" . $m;
                $geo = new $class();

                /**
                 * @var Service $geo
                 */
                $res = $geo->lookup($ip, $options);
                /**
                 * @var GeoObject $res
                 */

                $this->lastResponse = $res;
                $this->accumulate();
                $complete = true;
                $notfound = array();
                foreach ($properties as $pr) {
                    if ($this->{'is' . ucfirst($pr) . 'Required'} === true && !$this->accumulativeGeo->{$pr}) {
                        $complete = false;
                        $notfound[] = $pr;
                        break;
                    }
                }
                if (!$complete) {
                    $this->errors[$m] = $m . ' found' . print_r($this->lastResponse, true) . '; Not all required properties found(' . implode(',', $notfound) . ')';
                    continue;
                }

                return $this->accumulativeGeo;
            } catch (GeoException $ex) {
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
        foreach ($this->lastResponse as $propName => $val) {
            if (!isset($this->accumulativeGeo->{$propName}) || empty($this->accumulativeGeo->{$propName})) {
                $this->accumulativeGeo->{$propName} = $val;
            }
            if ($propName == 'method') {
                $this->accumulativeGeo->method = $val;
            }
        }
        return $this;
    }

    /**
     *
     * @return \GeoServices\GeoObject
     */
    public function getFound() {
        return $this->accumulativeGeo;
    }

}
