<?php

namespace GeoServices\Services;

/**
 *
 * @author kubrey
 */
interface Service {

    public function lookup($ip, $options = array());

}
