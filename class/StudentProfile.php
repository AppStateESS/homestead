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
     * $roommates is the focus of getProfileView(). It's structure (below) is helpful in
     * StudentProfileView.  It also makes it a little easier to recognize which roommmates
     * are requested ones so they can be emphasized in the template (admin/fancy-student-info.tpl)
     * Note that a student can only have a single pending/confirmed roommate request but multiple 
     * assigned roommates!
     *
     *     roommates => ['PENDING'] => ROOMMATE
     *               => ['CONFIRMED'] => ROOMMATE
     *               => ['NEITHER'][...]
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
      
        $pendingRoommate = HMS_Roommate::get_pending_roommate($studentUsername, $this->term);
        $confirmedRoommate = HMS_Roommate::get_confirmed_roommate($studentUsername, $this->term);

        //
        // If student is assigned to room...
        //
        if(!is_null($assignment)){
            // TODO: add a function for this somewhere.
            $assignedRoommates = $assignment->get_parent()->get_parent()->get_assignees();
            
            foreach($assignedRoommates as $roomie){
                $rm = null;

                // make sure $roomie isn't the student being profiled
                if($roomie != FALSE && $roomie->getUsername() != $studentUsername){

                    // Get student object and room link
                    $rm = StudentFactory::getStudentByUsername($roomie->getUsername(), $this->term);
                    $roomLink = $this->getRoommateRoomLink($rm->getUsername());
                    
                    // if $roomie is pending request
                    if(!is_null($pendingRoommate) && $roomie->getUsername() == $pendingRoomate->getUsername()){
                        $roommates['PENDING'] = $rm->getFullNameProfileLink() . " - $roomLink (Pending)";
                    }
                    // if $roomie is confirmed request
                    else if (!is_null($confirmedRoommate) && $roomie->getUsername() == $confirmedRoommate->getUsername()){
                        $roommates['CONFIRMED'] = $rm->getFullNameProfileLink() . " - $roomLink (Confirmed)";
                    }
                    // if $roomie was assigned but not requested
                    else {
                        $roommates['NEITHER'][] = $rm->getFullNameProfileLink() . " - $roomLink";
                    }
                }
            }
        }
        //
        // If student is NOT assigned to room...
        //
        else{
            if(!is_null($pendingRoommate)){
                $pendingStudent = StudentFactory::getStudentByUsername($pendingRoommate, $this->term);
                $roommates['PENDING'] = $pendingStudent->getFullNameProfileLink() . " (Pending)";
            } 
            else if(!is_null($confirmedRoommate)){
                $roommates['CONFIRMED'] = $pendingStudent->getFullNameProfileLink() . " (Confirmed)";
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
        $rmAssignment->loadBed();
        $editRoomCmd = CommandFactory::getCommand('EditRoomView');
        $editRoomCmd->setRoomId($rmAssignment->_bed->room_id);
        $roomLink = $editRoomCmd->getLink($rmAssignment->_bed->bedroom_label);
        return $roomLink;
    }

}