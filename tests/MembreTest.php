<?php

namespace PierreGranger\ApidaeEvent\Tests ;

use PHPUnit\Framework\TestCase;
use PierreGranger\ApidaeEventMM ;

class MembreTest extends TestCase {

    protected static ApidaeEventMM $apidaeEvent ;
    
    public static function setUpBeforeClass(): void
    {
        global $apidaeEvent ;
        include(realpath(dirname(__FILE__)) . '/../requires.inc.php');
        self::$apidaeEvent = $apidaeEvent ;
    }

    public function testGetMembresFromCommune() {
        $membres = self::$apidaeEvent->getMembresFromCommuneInsee('03190',true) ;
        $this->assertTrue(sizeof($membres) > 4) ;
        $membre = array_shift($membres) ;
        $this->assertArrayHasKey('secteur',$membre) ;

        $membres = self::$apidaeEvent->getMembresFromCommuneInsee('30211',true) ;
        $this->assertTrue(sizeof($membres) > 4) ;
        $membre = array_shift($membres) ;
        $this->assertArrayHasKey('secteur',$membre) ;

    }

}