<?php

namespace Homestead\applicationFeature;

use \Homestead\ApplicationFeatureRegistration;

class CreateProfileRegistration extends ApplicationFeatureRegistration {
    public function __construct()
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
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return true;
        }

        return false;
    }
}

class CreateProfile extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'RoommateProfile.php');
        $profile = RoommateProfileFactory::getProfile($student->getBannerID(), $this->term);

        PHPWS_Core::initModClass('hms', 'StudentMenuProfileView.php');
        return new StudentMenuProfileView($student, $this->getStartDate(), $this->getEndDate(), $this->term, $profile);
    }

}
