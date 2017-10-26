<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;
use \Homestead\Term;

class CreateProfileRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'CreateProfile';
        $this->description = 'Create Student Profile';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 3;
    }

    public function showForStudent(Student $student, $term)
    {
        // New Incoming Freshmen
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return true;
        }

        return false;
    }
}
