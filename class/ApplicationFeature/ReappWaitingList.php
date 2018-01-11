<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\UserStatus;
use \Homestead\HousingApplicationFactory;
use \Homestead\ReapplicationWaitingListMenuBlockView;

class ReappWaitingList extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $term = \PHPWS_Settings::get('hms', 'lottery_term');
        $application = HousingApplicationFactory::getAppByStudent($student, $term, 'lottery');

        return new ReapplicationWaitingListMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
