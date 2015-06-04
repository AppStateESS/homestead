<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class RoomChangeRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'RoomChange';
        $this->description = 'Room Change';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 7;
    }

    public function showForStudent(Student $student, $term)
    {
        return true;
    }
}

class RoomChange extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeMenuBlockView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');

        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);

        $request = RoomChangeRequestFactory::getPendingByStudent($student, $this->term);

        return new RoomChangeMenuBlockView($student, $this->term, $this->getStartDate(), $this->getEndDate(), $assignment, $request);
    }
}

