<?php

PHPWS_Core::initModClass('hms', 'exception/HMSException.php');

class StudentNotFoundException extends HMSException {

    private $requestedId;

    public function __construct($message, $code = 0, $requestedId = null){
        parent::__construct($message, $code);

        $this->requestedId = $requestedId;
    }

    public function getRequestedId()
    {
        return $this->requestedId;
    }
}

?>