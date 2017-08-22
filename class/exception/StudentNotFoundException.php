<?php

namespace Homestead\exception;

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
