<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;

class RoomChangeRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'RoomChange';
        $this->description = 'Room Change';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 8;
    }

    public function showForStudent(Student $student, $term)
    {
        return true;
    }
}
