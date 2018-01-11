<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;
use \Homestead\Term;

class RlcReapplicationRegistration extends ApplicationFeatureRegistration {
    public function __construct()
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
