<?php

namespace GeoServices\Services;

/**
 *
 * @author kubrey
 */
interface Service {

    /**
     * 
     * @param string $ip
     * @param array $options Additional information possibly needed in this method(db path, etc)
     * @return GeoObject
     * @throws GeoException
     */
    public function lookup($ip, $options = array());
}
