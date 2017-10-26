<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\UserStatus;
use \Homestead\HousingApplication;
use \Homestead\ReapplicationWaitingListMenuBlockView;

class ReappWaitingList extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $term = \PHPWS_Settings::get('hms', 'lottery_term');
        $application = HousingApplication::getApplicationByUser(UserStatus::getUsername(), $term, 'lottery');

        return new ReapplicationWaitingListMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
