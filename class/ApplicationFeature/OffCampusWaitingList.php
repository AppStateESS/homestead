<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\HousingApplicationFactory;
use \Homestead\OffCampusWaitingListMenuBlockView;

class OffCampusWaitingList extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $application = HousingApplicationFactory::getAppByStudent($student, $this->term);

        return new OffCampusWaitingListMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
