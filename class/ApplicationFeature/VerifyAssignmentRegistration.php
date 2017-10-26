<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;

class VerifyAssignmentRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'VerifyAssignment';
        $this->description = 'Verify Assignment';
        $this->startDateRequired = true;
        $this->endDateRequired = false;
        $this->priority = 6;
    }

    public function showForStudent(Student $student, $term)
    {
        return true;
    }
}
