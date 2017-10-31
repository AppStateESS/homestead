<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\HMS_Assignment;
use \Homestead\HousingApplicationFactory;
use \Homestead\LotteryApplication;
use \Homestead\HMS_Lottery;
use \Homestead\ReapplicationMenuBlockView;

class Reapplication extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $assignment       = HMS_Assignment::getAssignment($student->getUsername(), $this->term);
        $application      = HousingApplicationFactory::getAppByStudent($student, $this->term);

        if(!$application instanceof LotteryApplication){
            $application = null;
        }

        $roommateRequests = HMS_Lottery::get_lottery_roommate_invites($student->getUsername(), $this->term);

        return new ReapplicationMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $assignment, $application, $roommateRequests);
    }
}
