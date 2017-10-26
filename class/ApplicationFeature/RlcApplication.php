<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\HousingApplicationFactory;
use \Homestead\HMS_RLC_Application;
use \Homestead\HMS_RLC_Assignment;
use \Homestead\RlcApplicationMenuView;

class RlcApplication extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        // Get a housing application, if one exists
        $housingApp = HousingApplicationFactory::getAppByStudent($student, $this->getTerm());

        // Get an rlc application if one exists
        $application = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $this->getTerm());

        // Check for an assignment
        $assignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $this->getTerm());

        return new RlcApplicationMenuView($this->term, $student, $this->getStartDate(), $this->getEditDate(), $this->getEndDate(), $application, $assignment, $housingApp);
    }
}
