<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class ReappWaitingListRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'ReappWaitingList';
        $this->description = 'Re-application Waiting List';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 1;
    }
    
    public function showForStudent(Student $student, $term)
    {
        // for freshmen
        if($term == $student->getApplicationTerm())
        {
            return true;
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        
        $assignment = HMS_Assignment::checkForAssignment($student->getUsername(), $term);
        
        // for returning students (summer terms)
        if($term > $student->getApplicationTerm() && $assignment !== TRUE){
            return true;
        }
        
        return false;
    }
}

class ReappWaitingList extends ApplicationFeature {
    
    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'ReapplicationWaitingListMenuBlockView.php');
        
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $application = HousingApplication::getApplicationByUser(UserStatus::getUsername(), $term);
        
        return new ReapplicationWaitingListMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
?>
