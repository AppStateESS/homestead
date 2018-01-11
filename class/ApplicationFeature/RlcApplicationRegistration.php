<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;
use \Homestead\Term;

class RlcApplicationRegistration extends ApplicationFeatureRegistration {
    public function __construct()
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
