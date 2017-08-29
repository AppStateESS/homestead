<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\HousingApplication;
use \Homestead\ApplicationMenuBlockView;
use \Homestead\Student;

class Application extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $application      = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);

        return new ApplicationMenuBlockView($this->term, $this->getStartDate(), $this->getEditDate(), $this->getEndDate(), $application);
    }
}
