<?php

namespace GeoServices\Services;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-09-10 at 20:58:27.
 */
class MaxmindOldTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var MaxmindOld
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new MaxmindOld;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @expectedException \GeoServices\GeoException
     */
    public function testInvalidIpException() {
        $this->object->lookup('1.2.3');
    }

    /**
     * @expectedException \GeoServices\GeoException
     */
    public function testNonExistingDb() {
        $db = 'maxminddb.dat';
        $this->object->lookup('1.1.1.1', array('maxmindolddb' => $db));
    }

    public function testDbExists() {
        $dbReal = '/var/www/GeoLiteCity.dat';
        $this->assertFileExists($dbReal, 'Maxmind Legacy dat file does not exist');
    }

    public function testValidReturn() {
        $dbReal = '/var/www/GeoLiteCity.dat';
        $this->assertInstanceOf('GeoServices\GeoObject', $this->object->lookup('1.1.1.1', array('maxmindolddb' => $dbReal)), 'Return wrong instance');
    }

}
