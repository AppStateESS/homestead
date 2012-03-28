<?php

class StudentProfile {

    private $student;
    private $term;
    private $profileView;
    private $roommates = array();

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

        $studentUsername = $this->student->getUsername();
        $assignment = HMS_Assignment::getAssignmentByBannerId($this->student->getBannerId(), $this->term);

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
                if($roomie != FALSE && $roomie->getUsername() != $studentUsername){
                    $roomieUsername = $roomie->getUsername();
                    if(is_null($confirmedRoommate) || $roomieUsername != $confirmedRoommate->getUsername()){
                        if(is_null($pendingRoommate) || $roomieUsername != $pendingRoommate->getUsername()){
                            // Get student object and room link
                            $roomLink = $this->getRoommateRoomLink($roomie->getUsername());
                            // if $roomie was assigned but not requested
                            $this->roommates['ASSIGNED'][] = $roomie->getFullNameProfileLink() . " - $roomLink";
                        }
                    }
                }
            }
        }

        //
        // Check status of requested roommates
        //
        if(!is_null($confirmedRoommate)){
            if(!is_null($assignment)){
                $confirmedRmAssignment = HMS_Assignment::getAssignment($confirmedRoommate->getUsername(), $this->term);

                if(!is_null($confirmedRmAssignment)){
                    // if confirmed roommate is assigned to different room than profile student
                    if($assignment->get_parent()->room_id != $confirmedRmAssignment->get_parent()->room_id){
                        $this->setRoommateVar($confirmedRoommate, "confirmed", "mismatched_rooms");
                    }
                    else{
                        $this->setRoommateVar($confirmedRoommate, "confirmed");
                    }
                }
                else{
                    // if profile student's room is full
                    if(!$assignment->get_parent()->get_parent()->has_vacancy()){
                        $this->setRoommateVar($confirmedRoommate, "confirmed", "no_bed_available");
                    }
                    else{
                        $this->setRoommateVar($confirmedRoommate, "confirmed");
                    }
                }
            }
            // assignment is null
            else {
                $this->setRoommateVar($confirmedRoommate, "confirmed");
            }
        }
        else if (!is_null($pendingRoommate)){
            if(!is_null($assignment)){
                $pendingRmAssignment = HMS_Assignment::getAssignment($pendingRoommate->getUsername(), $this->term);

                if(!is_null($pendingRmAssignment)){
                    // if pending roommate is assigned to different room than profile student
                    if($assignment->get_parent()->room_id != $pendingRmAssignment->get_parent()->room_id){
                        $this->setRoommateVar($pendingRoommate, "pending", "mismatched_rooms");
                    }
                    else{
                        $this->setRoommateVar($pendingRoommate, "pending");
                    }
                }
                else{
                    // if profile student's room is full
                    if(!$assignment->get_parent()->get_parent()->has_vacancy()){
                        $this->setRoommateVar($pendingRoommate, "pending", "no_bed_available");
                    }
                    else{
                        $this->setRoommateVar($pendingRoommate, "pending");
                    }
                }
            }
            // assignment is null
            else {
                $this->setRoommateVar($pendingRoommate, "pending");
            }
        }

        $applications = HousingApplication::getAllApplicationsForStudent($this->student);

        return new StudentProfileView($this->student, $applications, $assignment, $this->roommates);
    }


    /**
     * setRoommateVar will set the variable in $roommates properly
     * It can handle requested roommates that are confirmed/pending,
     * assigned to separate rooms or assigned to a room that has no more
     * beds left.
     */
    private function setRoommateVar($roomie, $status, $status_extra=null)
    {
        $roomLink = $this->getRoommateRoomLink($roomie->getUsername());
        if(is_null($roomLink)){
            $roomLink = "";
        }else{
            $roomLink = " - ".$roomLink;
        }

        if($status_extra == "no_bed_available"){
            if($status == "confirmed"){
                $this->roommates['NO_BED_AVAILABLE'] = $roomie->getFullNameProfileLink() . $roomLink . " (Confirmed | No Bed Available)";
            }
            else if($status == "pending"){
                $this->roommates['NO_BED_AVAILABLE'] = $roomie->getFullNameProfileLink() . $roomLink . " (Pending | No Bed Available)";
            }
        }
        else if($status_extra == "mismatched_rooms"){
            if($status == "confirmed"){
                $this->roommates['MISMATCHED_ROOMS'] = $roomie->getFullNameProfileLink() . $roomLink . " (Confirmed | Mismatched)";
            }
            else if($status == "pending"){
                $this->roommates['MISMATCHED_ROOMS'] = $roomie->getFullNameProfileLink() . $roomLink . " (Pending | Mismatched)";
            }
        }
        else {
            if($status == "confirmed"){
                $this->roommates['CONFIRMED'] = $roomie->getFullNameProfileLink() . $roomLink . " (Confirmed)";
            }
            else if($status == "pending"){
                $this->roommates['PENDING'] = $roomie->getFullNameProfileLink() . $roomLink . " (Pending)";
            }
        }
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