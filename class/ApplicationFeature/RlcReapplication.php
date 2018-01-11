<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\HousingApplicationFactory;
use \Homestead\LotteryApplication;
use \Homestead\HMS_RLC_Application;
use \Homestead\HMS_RLC_Assignment;
use \Homestead\RlcReapplicationMenuBlockView;

class RlcReapplication extends ApplicationFeature {

    public function getMenuBlockView(Student $student){

        $application = HousingApplicationFactory::getAppByStudent($student, $this->term);
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
