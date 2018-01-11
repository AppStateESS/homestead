<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;
use \Homestead\HousingApplicationFactory;
use \Homestead\Term;

class OffCampusWaitingListRegistration extends ApplicationFeatureRegistration {

    public function __construct()
    {
        $this->name = 'OffCampusWaitingList';
        $this->description = 'Open Waiting List';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 4;
    }

    public function showForStudent(Student $student, $term)
    {
        if($student->getApplicationTerm() > Term::getCurrentTerm()){
            return false;
        }

        $app = HousingApplicationFactory::getAppByStudent($student, $term);

        // Must be a returning student and either have not re-applied or have re-applied to the waiting list already
        if(($student->getApplicationTerm() <= Term::getCurrentTerm() && (is_null($app)) || (!is_null($app) && $app->application_type == 'offcampus_waiting_list'))){
            return true;
        }

        return false;
    }
}
