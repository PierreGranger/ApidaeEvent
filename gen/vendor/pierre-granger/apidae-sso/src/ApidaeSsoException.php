<?php

	nameSpace PierreGranger ;

/**
 * 
 * @author	Pierre Granger	<pierre@pierre-granger.fr>
 * 
 * 
 */
class ApidaeSsoException extends \Exception {

    protected $details ;

    public function __construct($message,$code=0,\Exception $previous=null,$details=null) {
        parent::__construct($message, $code, $previous) ;
        $this->details = $details ;
    }

    public function getDetails() {
        return $this->details ;
    }
    
}