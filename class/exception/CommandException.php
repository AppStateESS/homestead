<?php

PHPWS_Core::initModClass('hms', 'exception/HMSException.php');

class CommandException extends HMSException {
    
    public function __construct($message, $code = 0){
        parent::__construct($message, $code);
    }
}

?>