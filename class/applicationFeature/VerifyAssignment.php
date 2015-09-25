<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class VerifyAssignmentRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'VerifyAssignment';
        $this->description = 'Verify Assignment';
        $this->startDateRequired = true;
        $this->endDateRequired = false;
        $this->priority = 6;
    }

    public function showForStudent(Student $student, $term)
    {
        // Freshmen only
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return true;
        }

        return false;
    }
}

class VerifyAssignment extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'VerifyAssignmentMenuBlockView.php');
        return new VerifyAssignmentMenuBlockView($student, $this->getStartDate(), $this->getEndDate());
    }

}
