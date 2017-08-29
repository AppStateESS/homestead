<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeature;
use \Homestead\Student;
use \Homestead\HMS_RLC_Assignment;
use \Homestead\HMS_Assignment;
use \Homestead\HMS_Lottery;
use \Homestead\RlcSelfSelectionMenuBlockView;

class RlcSelfSelection extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $this->getTerm());

        $roomAssignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $this->getTerm());

        $roommateRequests = HMS_Lottery::get_lottery_roommate_invites($student->getUsername(), $this->term);

        return new RlcSelfSelectionMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $rlcAssignment, $roomAssignment, $roommateRequests);
    }
}
