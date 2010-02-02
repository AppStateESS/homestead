<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class SearchProfilesRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'SearchProfiles';
        $this->description = 'Search Student Profiles';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 4;
    }

    public function showForStudent(Student $student, $term)
    {
        // For freshmen
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return true;
        }

        return false;
    }
}

class SearchProfiles extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'SearchProfilesMenuBlockView.php');
        return new SearchProfilesMenuBlockView($student, $this->getStartDate(), $this->getEndDate());
    }

}

?>
