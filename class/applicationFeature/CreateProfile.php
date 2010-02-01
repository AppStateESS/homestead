<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class CreateProfileRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'CreateProfile';
        $this->description = 'Create Student Profile';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 3;
    }

    public function showForStudent(Student $student, $term)
    {
        // New Incoming Freshmen
        if($term == $student->getApplicationTerm())
        {
            return true;
        }

        return false;
    }
}

class CreateProfile extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        return 'Create Profile';
    }

}

?>
