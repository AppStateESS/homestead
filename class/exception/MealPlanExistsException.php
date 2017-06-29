<?php

PHPWS_Core::initModClass('hms', 'exception/HMSException.php');

class MealPlanExistsException extends HMSException {

    public function __construct($message, $code){
        parent::__construct($message, $code);
    }

}
