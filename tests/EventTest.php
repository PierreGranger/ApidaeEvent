<?php

namespace PierreGranger\ApidaeEvent\Tests ;

use PHPUnit\Framework\TestCase;
use PierreGranger\ApidaeEvent ;

class EventTest extends TestCase {

    protected static ApidaeEvent $apidaeEvent ;
    
    public static function setUpBeforeClass(): void
    {
        global $apidaeEvent ;
        include(realpath(dirname(__FILE__)) . '/../requires.inc.php');
        self::$apidaeEvent = $apidaeEvent ;
    }

    public function testGetElementsReferenceByType() {
        $elements = self::$apidaeEvent->getElementsReferenceByType('TypeClientele') ;
        $this->assertTrue(sizeof($elements) > 40) ;
        $element = array_shift($elements) ;
        $this->assertArrayHasKey('ordre',$element) ;
    }

}