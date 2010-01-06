<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class RLCApplicationFeatureRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'RLCApplicationFeature';
        $this->description = 'RLC Applications';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 2;
        $this->allowedTypes = array('F');
    }
}

class RLCApplicationFeature extends ApplicationFeature {
    
    public function getMenuBlockView(Student $student)
    {
       //TODO
    }
}

?>