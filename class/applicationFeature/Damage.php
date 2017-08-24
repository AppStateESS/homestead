<?php

namespace Homestead\applicationFeature;

use \Homestead\ApplicationFeatureRegistration;

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

class Damage extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeMenuBlockView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');

        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);

        return new DamageMenuBlockView($student, $this->term, $this->getStartDate(), $this->getEndDate(), $assignment);
    }
}
