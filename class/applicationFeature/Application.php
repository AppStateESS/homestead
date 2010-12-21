<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class ApplicationRegistration extends ApplicationFeatureRegistration {
	function __construct()
	{
		$this->name = 'Application';
		$this->description = 'Application';
		$this->startDateRequired = true;
        $this->editDateRequired = true;
		$this->endDateRequired = true;
		$this->priority = 1;
	}

    public function showForStudent(Student $student, $term)
    {
        // for freshmen
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return true;
        }

        // for returning students (summer terms)
        if($term > $student->getApplicationTerm() && $term != PHPWS_Settings::get('hms', 'lottery_term') && (Term::getTermSem($term) == TERM_SUMMER1 || Term::getTermSem($term) == TERM_SUMMER2)){
            return true;
        }

        return false;
    }
}

class Application extends ApplicationFeature {

	public function getMenuBlockView(Student $student)
	{
		PHPWS_Core::initModClass('hms', 'HousingApplication.php');
		PHPWS_Core::initModClass('hms', 'ApplicationMenuBlockView.php');

		$application      = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);

		return new ApplicationMenuBlockView($this->term, $this->getStartDate(), $this->getEditDate(), $this->getEndDate(), $application);
	}
}
?>
