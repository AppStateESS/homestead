<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class RlcApplicationRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'RlcApplication';
        $this->description = 'RLC Applications';
        $this->startDateRequired = true;
        $this->editDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 2;
    }
    
    public function showForStudent(Student $student, $term)
    {
        if($student->getType() != TYPE_FRESHMEN && $student->getType() != TYPE_TRANSFER) {
            return false;
        }

        // Application term must be in the future
        if($student->getApplicationTerm() <= Term::getCurrentTerm()){
            return false;
        }

        $sem = substr($term, 4, 2);
        if($sem != TERM_SUMMER1 && $sem != TERM_SUMMER2 && $sem != TERM_FALL) {
            return false;
        }
        
        return true;
    }
}

class RlcApplication extends ApplicationFeature {
    
    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        $application = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $student->getApplicationTerm());

        PHPWS_Core::initModClass('hms', 'RlcApplicationMenuView.php');
        return new RlcApplicationMenuView($student, $this->getStartDate(), $this->getEditDate(), $this->getEndDate(), $application);
    }
}

?>
