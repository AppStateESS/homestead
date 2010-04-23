<?php

class StudentProfile {

	private $student;
	private $term;
	private $profileView;

	public function __construct(Student $student, $term){
		$this->student = $student;
		$this->term = $term;
	}

	public function getProfileView()
	{
		PHPWS_Core::initModClass('hms', 'StudentProfileView.php');
		PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
		PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
		PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

		$assignment = HMS_Assignment::getAssignment($this->student->getUsername(), $this->term);
		
		$roommates = array();
		$pendingRoommates = HMS_Roommate::get_pending_roommate($this->student->getUsername(), $this->term);
		$confirmedRoommates = HMS_Roommate::get_confirmed_roommate($this->student->getUsername(), $this->term);

		if(!is_null($assignment)){
			$assignedRoommates = $assignment->get_parent()->get_parent()->get_assignees();
				
			foreach($assignedRoommates as $roomie){
				$rm = null;
				if($roomie !== FALSE && $roomie->getUsername() != $this->student->getUsername()){
					$rm = StudentFactory::getStudentByUsername($roomie->getUsername(), $this->term);
                    $roomLink = $this->getRoommateRoomLink($rm->getUsername());
					if(!is_null($pendingRoommates) && $roomie->getUsername() == $pendingRoommates->getUsername()){
						$roommates[] =  $rm->getFullNameProfileLink() . " - $roomLink (Pending)";
					} else if(!is_null($confirmedRoommates) && $roomie->getUsername() == $confirmedRoommates->getUsername()){
						$roommates[] = $rm->getFullNameProfileLink() . " - $roomLink (Confirmed)";
					}else{
						$roommates[] = $rm->getFullNameProfileLink() . " - $roomLink";
					}
				}
			}
		} else {
			if(!is_null($pendingRoommates)){
			    $pendingStudent = StudentFactory::getStudentByUsername($pendingRoommates, $this->term);
                $roomLink = $this->getRoommateRoomLink($pendingStudent->getUsername());
				$roommates[] = $pendingStudent->getFullNameProfileLink() . " - $roomLink (Pending)";
			} else if(!is_null($confirmedRoommates)){
				$roommates[] = $confirmedRoommates->getFullNameProfileLink() . " - $roomLink (Confirmed)";
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