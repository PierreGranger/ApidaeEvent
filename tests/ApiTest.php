<?php

namespace PierreGranger\ApidaeEvent\Tests ;

use PHPUnit\Framework\TestCase;
use PierreGranger\ApidaeEvent ;

class ApiTest extends TestCase {

    protected static ApidaeEvent $apidaeEvent ;
    
    public static function setUpBeforeClass(): void
    {
        global $apidaeEvent ;
        include(realpath(dirname(__FILE__)) . '/../requires.inc.php');
        self::$apidaeEvent = $apidaeEvent ;
    }

    public function testGetCommunesByTerritoire() {
        $communes = self::$apidaeEvent->getCommunesByTerritoire(4784427,true) ;
        $this->assertTrue(sizeof($communes) > 10) ;
        $commune = array_shift($communes) ;
        $this->assertArrayHasKey('codePostal',$commune) ;
    }

    public function testGetComunesByInsee() {
        $communes = self::$apidaeEvent->getCommunesByInsee(['03002','03012','03036'],true) ;
        $this->assertCount(3,$communes) ;
        $commune = array_shift($communes) ;
        $this->assertArrayHasKey('codePostal',$commune) ;
    }

    public function testGetCommunesById() {
        $communes = self::$apidaeEvent->getCommunesById([1237,1247,1271],true) ;
        $this->assertCount(3,$communes) ;
        $commune = array_shift($communes) ;
        $this->assertArrayHasKey('codePostal',$commune) ;
    }

    public function testGetOffre() {
        $offre = self::$apidaeEvent->getOffre(760958,null,true) ;
        $this->assertArrayHasKey('id',$offre) ;
    }

}