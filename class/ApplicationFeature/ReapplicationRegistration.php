<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Term;
use \Homestead\Student;

class ReapplicationRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'Reapplication';
        $this->description = 'Re-application';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 1;
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
