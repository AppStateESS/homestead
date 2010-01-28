<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class RlcApplicationRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'RlcApplication';
        $this->description = 'RLC Applications';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 2;
    }
    
    public function showForStudent(Student $student, $term)
    {
        if($student->getType() == TYPE_FRESHMEN && $term == $student->getApplicationTerm()){        
            return true;
        }
        
        return false;
    }
}

class RlcApplication extends ApplicationFeature {
    
    public function getMenuBlockView(Student $student)
    {
       //TODO
    }
}

?>
