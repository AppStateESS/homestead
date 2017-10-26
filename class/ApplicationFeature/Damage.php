<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\HMS_Assignment;
use \Homestead\Student;
use \Homestead\DamageMenuBlockView;


class Damage extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);

        return new DamageMenuBlockView($student, $this->term, $this->getStartDate(), $this->getEndDate(), $assignment);
    }
}
