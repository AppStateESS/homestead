<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;

class DamageRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'Damage';
        $this->description = 'Room Damage Self-Reporting';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 7;
    }

    public function showForStudent(Student $student, $term)
    {
        // Set up the check for whether it is in the 48 hour period.
        return true;
    }
}
