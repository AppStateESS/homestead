<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\HousingApplicationFactory;
use \Homestead\UpdateEmergencyContactMenuBlockView;

class UpdateEmergencyContact extends ApplicationFeature {
    public function getMenuBlockView(Student $student)
    {
        $application = HousingApplicationFactory::getAppByStudent($student, $this->term);

        return new UpdateEmergencyContactMenuBlockView($student, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
