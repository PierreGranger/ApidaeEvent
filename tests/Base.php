<?php

namespace PierreGranger\ApidaeEvent\Tests ;

use PHPUnit\Framework\TestCase;
use PierreGranger\ApidaeEvent ;

class Base extends TestCase
{
    protected ApidaeEvent $apidaeEvent ;
    
    public function setUp(): void
    {
        include(realpath(dirname(__FILE__)) . '/../requires.inc.php');
        $this->apidaeEvent = new ApidaeEvent(array_merge($config, ['handler' => $handlerStack]));
    }
}
