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
					if(!is_null($pendingRoommates) && $roomie->getUsername() == $pendingRoommates){
						$roommates[] =  $rm->getFullNameProfileLink() . ' (Pending)';
					} else if(!is_null($confirmedRoommates) && $roomie->getUsername() == $confirmedRoommates){
						$roommates[] = $rm->getFullNameProfileLink() . '(Confirmed)';
					}else{
						$roommates[] = $rm->getFullNameProfileLink();
					}
				}
			}
		} else {
			if($pendingRoommates != NULL){
				$pendingStudent = StudentFactory::getStudentByUsername($roomie, $this->term);
				$roommates[] = $pendingStudent->getFullNameProfileLink() . ' (Pending)';
			} else if(!is_null($confirmedRoommates)){
				$confirmedStudent = StudentFactory::getStudentByUsername($roomie, $this->term);
				$roommates[] = $confirmedStudent->getFullNameProfileLink() . '(Confirmed)';
			}
		}

		$applications = HousingApplication::getAllApplications($this->student->getUsername());

		return new StudentProfileView($this->student, $applications, $assignment, $roommates);
	}

}