<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\HMS_Assignment;
use \Homestead\RoomChangeRequestFactory;
use \Homestead\RoomChangeMenuBlockView;

class RoomChange extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);

        $request = RoomChangeRequestFactory::getPendingByStudent($student, $this->term);

        return new RoomChangeMenuBlockView($student, $this->term, $this->getStartDate(), $this->getEndDate(), $assignment, $request);
    }
}
