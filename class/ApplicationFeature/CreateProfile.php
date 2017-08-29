<?php

namespace Homestead\ApplicationFeature;

use \Homestead\Student;
use \Homestead\RoommateProfile;
use \Homestead\StudentMenuProfileView;
use \Homestead\ApplicationFeature;

class CreateProfile extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $profile = RoommateProfileFactory::getProfile($student->getBannerID(), $this->term);

        return new StudentMenuProfileView($student, $this->getStartDate(), $this->getEndDate(), $this->term, $profile);
    }

}
