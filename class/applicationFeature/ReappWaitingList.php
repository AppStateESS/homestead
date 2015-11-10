<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class ReappWaitingListRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'ReappWaitingList';
        $this->description = 'Re-application Waiting List';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 2;
    }

    public function showForStudent(Student $student, $term)
    {
        // for freshmen
        if($student->getApplicationTerm() > Term::getCurrentTerm())
        {
            return false;
        }

        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');

        $application = HousingApplication::checkForApplication($student->getUsername(), $term);
        $assignment = HMS_Assignment::checkForAssignment($student->getUsername(), $term);

        // for returning students (summer terms)
        if($term > $student->getApplicationTerm() && $assignment !== TRUE && $application !== FALSE){
            return true;
        }

        return false;
    }
}

class ReappWaitingList extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'ReapplicationWaitingListMenuBlockView.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');

        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $application = HousingApplication::getApplicationByUser(UserStatus::getUsername(), $term, 'lottery');

        return new ReapplicationWaitingListMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
