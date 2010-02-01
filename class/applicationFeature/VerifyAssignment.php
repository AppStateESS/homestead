<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class VerifyAssignmentRegistration extends ApplicationFeatureRegistration {
    function __construct()
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
        if($term == $student->getApplicationTerm())
        {
            return true;
        }

        return false;
    }
}

class VerifyAssignment extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        return 'Verify Assignment';
    }

}

?>

