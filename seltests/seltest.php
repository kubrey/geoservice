<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Description of seltest
 *
 * @author kubrey
 */
class seltest extends PHPUnit_Extensions_SeleniumTestCase {

    function setUp() {
        $this->setBrowser("*chrome");
        $this->setBrowserUrl("http://ipservice.loc/");
    }

    function testMyTestCase() {
        $this->open("/");
        $this->assertTrue(true);
    }

}