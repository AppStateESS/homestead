<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\RoommateProfileFactory;
use \Homestead\SearchProfilesMenuBlockView;

class SearchProfiles extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $profile = RoommateProfileFactory::getProfile($student->getBannerID(), $this->term);

        return new SearchProfilesMenuBlockView($student, $this->getStartDate(), $this->getEndDate(), $profile, $this->term);
    }

}
