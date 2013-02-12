<?php

class SOAPException extends Exception{

    public function __construct($message, $code = 0, $functionName, $params){
        parent::__construct($message, $code);
    }
}

?>
