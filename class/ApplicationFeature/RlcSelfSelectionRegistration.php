<?php

namespace Homestead\ApplicationFeature;

use \Homestead\ApplicationFeatureRegistration;
use \Homestead\Student;
use \Homestead\Term;

class RlcSelfSelectionRegistration extends ApplicationFeatureRegistration {

	public function __construct()
    {
    	$this->name = 'RlcSelfSelection';
        $this->description = 'RLC Self-selection';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 5;
    }

    public function showForStudent(Student $student, $term)
    {
    	if($student->getApplicationTerm() <= Term::getCurrentTerm()){
    		return true;
    	} else {
    		return false;
    	}
    }
}
