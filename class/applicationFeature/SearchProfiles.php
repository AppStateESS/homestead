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
        if($term == $student->getApplicationTerm())
        {
            return true;
        }

        return false;
    }
}

class SearchProfiles extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        return 'Search Profiles';
    }

}

?>
