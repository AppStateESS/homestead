<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class RlcReapplicationRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'RlcReapplication';
        $this->description = 'RLC Re-application';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 3;
    }

    public function showForStudent(Student $student, $term)
    {
        if($student->getApplicationTerm() <= Term::getCurrentTerm()){
            return true;
        }

        if($student->getApplicationTerm() > Term::getCurrentTerm()){
            return false;
        }

        return false;
    }
}

class RlcReapplication extends ApplicationFeature {

    public function getMenuBlockView(Student $student){

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'RlcReapplicationMenuBlockView.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $application = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);
        if(!$application instanceof LotteryApplication){
            $application = null;
        }

        $rlcApp = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $this->term);
        if(!$rlcApp instanceof HMS_RLC_Application){
            $rlcApp = null;
        }

        // Check for an assignment
        $assignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $this->getTerm());

        return new RlcReapplicationMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application, $rlcApp, $assignment);
    }
}

?>
