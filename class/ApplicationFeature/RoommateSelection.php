<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\RoommateSelectionMenuBlockView;

class RoommateSelection extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        return new RoommateSelectionMenuBlockView($student, $this->getStartDate(), $this->getEditDate(), $this->getEndDate(), $this->getTerm());
    }

}
