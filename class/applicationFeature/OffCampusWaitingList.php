<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class OffCampusWaitingListRegistration extends ApplicationFeatureRegistration {
    
    function __construct()
    {
        $this->name = 'OffCampusWaitingList';
        $this->description = 'Off-campus Waiting List';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 2;
    }
    
    public function showForStudent(Student $student, $term)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        
        if($student->getApplicationTerm() > Term::getCurrentTerm()){
            return false;
        }
        
        // Must be a returning student and must be in-eligible
        if($student->getApplicationTerm() <= Term::getCurrentTerm() && !HMS_Lottery::determineEligibility($student->getUsername())){
            return true;
        }
        
        return false;
    }
}

class OffCampusWaitingList extends ApplicationFeature {
    
    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'OffCampusWaitingListMenuBlockView.php');
        
        $application = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);
        
        return new OffCampusWaitingListMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
    }
}

?>