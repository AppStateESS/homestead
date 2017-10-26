<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;

class UpdateEmergencyContactRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'UpdateEmergencyContact';
        $this->description = 'Update Emergency & Missing Persons Contact';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 9;
    }

    public function showForStudent(Student $student, $term)
    {
        return true;
    }
}
