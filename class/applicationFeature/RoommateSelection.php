<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class RoommateSelectionRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'RoommateSelection';
        $this->description = 'Roommate Selection';
        $this->startDateRequired = true;
        $this->editDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 5;
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

class RoommateSelection extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'RoommateSelectionMenuBlockView.php');
        return new RoommateSelectionMenuBlockView($student, $this->getStartDate(), $this->getEditDate(), $this->getEndDate(), $this->getTerm());
    }

}

?>
