<?php

namespace Homestead\Exception;

class MealPlanExistsException extends HMSException {

    public function __construct($message, $code){
        parent::__construct($message, $code);
    }

}
