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
        /*
        // Freshmen only
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return true;
        }
        */

        return true;
    }
}

class RoomChange extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeMenuBlockView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');

        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);

        $changeReq = RoomChangeRequest::search($student->getUsername());

        return new RoomChangeMenuBlockView($student, $this->getStartDate(), $this->getEndDate(), $assignment, $changeReq);
    }
}

?>