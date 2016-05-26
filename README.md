# Geo data detection by IP

### Searching geo data by IP address ###

### Description ###
Include several services:

 - Maxmind
 - Ipgeobase.ru
 - Ipinfo.io
 - Freegeoip.net
 - Geobytes.com
 - Telize.com
 - Maxmind Legacy(v1)
 - IpApi(ip-api.com)


### Installation ###

 [Composer](http://getcomposer.org/).
 
Add  `kubrey/geoservice` to your `composer.json`

OR 


```
composer require kubrey/geoservice
```

If you have no composer, install beforehand:

Run in the project's root:

```
curl -s http://getcomposer.org/installer | php
```

And then :

```
php composer.phar init
```


### Usage ###

```
require 'vendor/autoload.php';

use GeoServices\GeoService;
use GeoServices\GeoException;

$g = new GeoService();
try {
    //set maxmind v2  absolute path to mmdb file(if you want to use this method)
    $g->setMaxmindDb(dirname(__FILE__) . '/GeoLite2-City.mmdb');
    //set maxmind-legacy  absolute path to dat file(if you want to use this method)
    $g->setMaxmindOldDb(dirname(__FILE__).'/GeoLiteCity.dat');
    $data = $g->lookup('95.153.74.242');
    var_dump($data->city);
} catch (GeoException $ex) {
    echo $ex->getMessage();
}
```

`$data` is an instance of `GeoObject`, containing these properties:

 - countryName
 - city
 - latitude
 - longitude
 - zip 
 - regionName
 - countryCode
 - isp

To set your own method priority:

```
$g->maxmind = 1;//run first
$g->ipgeobaseru = false;//do not use
$g->ipinfo = 3;
```

To set required geo parameters

```
$g->isCityRequired = false; - поиск города не обязателен
```

### Tests ###

PHPUnit:


```
/usr/bin/php vendor/phpunit/phpunit/phpunit --colors --bootstrap vendor/autoload.php tests
```


