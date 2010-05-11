<?php

class StudentProfile {

    private $student;
    private $term;
    private $profileView;

    public function __construct(Student $student, $term){
        $this->student = $student;
        $this->term = $term;
    }

    /**
     * $roommates is the focus of getProfileView(). It's structure is helpful in
     * StudentProfileView.  It also makes it a little easier to recognize which roommmates
     * are requested ones so they can be emphasized in the template (admin/fancy-student-info.tpl)
     * Note that a student can only have a single pending/confirmed roommate request but multiple 
     * assigned roommates!
     *
     */
    public function getProfileView()
    {
        PHPWS_Core::initModClass('hms', 'StudentProfileView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $roommates = array();

        $studentUsername = $this->student->getUsername();        
        $assignment = HMS_Assignment::getAssignment($studentUsername, $this->term);
        $ass2 = HMS_Assignment::getAssignment("sally",$this->term);

        $pendingRoommate = HMS_Roommate::get_pending_roommate($studentUsername, $this->term);
        $confirmedRoommate = HMS_Roommate::get_confirmed_roommate($studentUsername, $this->term);

        if(!is_null($assignment)){
            $assignedRoommates = $assignment->get_parent()->get_parent()->get_assignees();
        }

        //
        // If student is assigned to room...
        //
        if(!is_null($assignment)){
            foreach($assignedRoommates as $roomie){
                // make sure $roomie isn't the student being profiled or the requested roomies
                $roomieUsername = $roomie->getUsername();
                if($roomie != FALSE && $roomieUsername != $studentUsername){
                    if(is_null($confirmedRoommate) || $roomieUsername != $confirmedRoommate->getUsername()){
                        if(is_null($pendingRoommate) || $roomieUsername != $pendingRoommate->getUsername()){
                            // Get student object and room link
                            $roomLink = $this->getRoommateRoomLink($roomie->getUsername());
                            // if $roomie was assigned but not requested
                            $roommates['ASSIGNED'][] = $roomie->getFullNameProfileLink() . " - $roomLink";
                        }
                    }
                }
            }
        }

        //
        // Check status of requested roommates
        //
        if(!is_null($pendingRoommate)){
            // Check if there is an available bed for the pending requested student
            if(is_null($assignment) || $assignment->get_parent()->get_parent()->has_vacancy()
               || in_array($pendingRoommate, $assignedRoommates)){
                $roomLink = $this->getRoommateRoomLink($pendingRoommate->getUsername());
                if(!is_null($roomLink))
                    $roommates['PENDING'] = $pendingRoommate->getFullNameProfileLink() . " - $roomLink (Pending)";
                else
                    $roommates['PENDING'] = $pendingRoommate->getFullNameProfileLink() . " (Pending)";
            } 
            else {
                $roomLink = $this->getRoommateRoomLink($pendingRoommate->getUsername());
                if(!is_null($roomLink))
                    $roommates['NO_BED_AVAILABLE'] = $pendingRoommate->getFullNameProfileLink() . " - $roomLink (Pending/No bed)";
                else
                    $roommates['NO_BED_AVAILABLE'] = $pendingRoommate->getFullNameProfileLink() . " (Pending/No Bed)";
            }
        }
        else if (!is_null($confirmedRoommate)){
            // Check if there is an available bed for the confirmed requested student
            if(is_null($assignment) || $assignment->get_parent()->get_parent()->has_vacancy()
               || in_array($confirmedRoommate, $assignedRoommates)){
                $roomLink = $this->getRoommateRoomLink($confirmedRoommate->getUsername());
                if(!is_null($roomLink))
                    $roommates['CONFIRMED'] = $confirmedRoommate->getFullNameProfileLink() . " - $roomLink (Confirmed)";
                else
                    $roommates['CONFIRMED'] = $confirmedRoommate->getFullNameProfileLink() . " (Confirmed)";
            }
            else {
                $roomLink = $this->getRoommateRoomLink($confirmedRoommate->getUsername());
                if(!is_null($roomLink))
                    $roommates['NO_BED_AVAILABLE'] = $confirmedRoommate->getFullNameProfileLink() . " - $roomLink (Confirmed/No bed)";
                else
                    $roommates['NO_BED_AVAILABLE'] = $confirmedRoommate->getFullNameProfileLink() . " (Confirmed/No Bed)";
            }

        }

        $applications = HousingApplication::getAllApplications($this->student->getUsername());

        return new StudentProfileView($this->student, $applications, $assignment, $roommates);
    }
    
    /**
     * Fetch a roommate's bedroom label and create a link to that room
     */
    private function getRoommateRoomLink($username)
    {
        // Get link for roommates' room
        $rmAssignment = HMS_Assignment::getAssignment($username, $this->term);
        if(!is_null($rmAssignment)){
            $rmAssignment->loadBed();
            $editRoomCmd = CommandFactory::getCommand('EditRoomView');
            $editRoomCmd->setRoomId($rmAssignment->_bed->room_id);
            $roomLink = $editRoomCmd->getLink($rmAssignment->_bed->bedroom_label);
            return $roomLink;
        } else {
            return null;
        }
    }

}