# Complex Geo PHP API #

### Реализация поиска geo-данных IP по нескольким гео-сервисам и локальным базам ###

### Описание ###
Включает в себя методы:

 - Maxmind
 - Ipgeobase
 - Ipinfo.io
 - Freegeoip.net
 - Geobytes.com
 - Telize.com


### Установка через Composer ###

#### Определение зависимостей ####

 [Composer](http://getcomposer.org/).
Для установки добавьте `kubrey/ipgeobase` в Ваш `composer.json`. Если этого файла нет, то создайте его в корне сайта

```json
{
    "require": {
        "kubrey/geoservice": "dev-master"
    },
    "repositories":[
        {
            "type":"git",
            "url":"https://bitbucket.org/kubrey/geoservice"
        }
    ]
}
```

#### Установка Composer ####

Выполнить в корне проекта: 

```
curl -s http://getcomposer.org/installer | php
```

#### Установка зависимостей ####

Выполнить в корне проекта: 

```
php composer.phar install
```

#### Автолоадер ####

Выполнить автозагрузку всех пакетов composer можно подключив скрипт:
```
require 'vendor/autoload.php';
```

### Применение ###

```

require 'vendor/autoload.php';

use GeoServices\GeoService;

$g = new GeoService();
try {
    //установка полного пути к базе maxmind (если она используется)
    $g->setMaxminDb(dirname(__FILE__) . '/GeoLite2-City.mmdb');
    $sec = $g->lookup('95.153.74.242');
    var_dump($sec->city);
} catch (Exception $ex) {
    echo $ex->getMessage();
}
```

#### Применение ####
Для установки произвольного порядка выполнения методов:

```
$g->maxmind = 1;//выполнить первым
$g->ipgeobaseru = false;//не использовать
$g->ipinfo = 3;
```

Если достаточно найти страну:

```
$g->search4City = false;
```


