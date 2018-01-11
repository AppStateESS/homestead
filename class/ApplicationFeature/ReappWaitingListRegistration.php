<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;
use \Homestead\Term;
use \Homestead\HousingApplication;
use \Homestead\HMS_Assignment;

class ReappWaitingListRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'ReappWaitingList';
        $this->description = 'Re-application Waiting List';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 2;
    }

    public function showForStudent(Student $student, $term)
    {
        // for freshmen
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return false;
        }

        $application = HousingApplication::checkForApplication($student->getUsername(), $term);
        $assignment = HMS_Assignment::checkForAssignment($student->getUsername(), $term);
        // for returning students (summer terms)
        if($term > $student->getApplicationTerm() && $assignment !== TRUE && $application !== FALSE){
            return true;
        }

        return false;
    }
}
