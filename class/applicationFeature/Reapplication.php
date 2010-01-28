<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class ReapplicationRegistration extends ApplicationFeatureRegistration {
	function __construct()
	{
		$this->name = 'Reapplication';
		$this->description = 'Re-application';
		$this->startDateRequired = true;
		$this->endDateRequired = true;
		$this->priority = 1;
	}
	
	public function showForStudent(Student $student, $term)
	{
	    if($term > $student->getApplicationTerm()){
	        return true;
	    }
	    
	    return false;
	}
}

class Reapplication extends ApplicationFeature {
	
	public function getMenuBlockView(Student $student)
	{
		PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
		PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
		PHPWS_Core::initModClass('hms', 'HousingApplication.php');
		PHPWS_Core::initModClass('hms', 'ReapplicationMenuBlockView.php');
		
		$assignment       = HMS_Assignment::getAssignment($student->getUsername(), $this->term);
		$application      = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);
		$roommateRequests = HMS_Lottery::get_lottery_roommate_invites($student->getUsername(), $this->term);
		
		return new ReapplicationMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $assignment, $application, $roommateRequests);
	}
}
