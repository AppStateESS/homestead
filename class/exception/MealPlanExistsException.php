<?php

namespace Homestead\exception;

class MealPlanExistsException extends HMSException {

    public function __construct($message, $code){
        parent::__construct($message, $code);
    }

}
