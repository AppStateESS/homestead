<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\VerifyAssignmentMenuBlockView;

class VerifyAssignment extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        return new VerifyAssignmentMenuBlockView($student, $this->getStartDate(), $this->getEndDate(), $this->term);
    }

}
