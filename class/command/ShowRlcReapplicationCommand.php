<?php

class ShowRlcReapplicationCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars(){
        return array('action'=>'ShowRlcReapplication', 'term'=>$this->term);
    }

    public function execute(CommandContext $context){

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcReapplicationView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $errorCmd = CommandFactory::getCommand('ShowStudentMenu');

        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Check deadlines
        $feature = ApplicationFeature::getInstanceByNameAndTerm('RlcReapplication', $this->term);
        if(is_null($feature) || !$feature->isEnabled()){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, RLC re-applications are not avaialable for this term.");
            $errorCmd->redirect();
        }
        
        if($feature->getStartDate() > mktime()){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, it is too soon to submit a RLC re-application.");
            $errorCmd->redirect();
        }else if($feature->getEndDate() < mktime()){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, the RLC re-application deadline has already passed. Please contact University Housing if you are interested in applying for a RLC.");
            $errorCmd->redirect();
        }
        
        # Double check the the student is eligible
        $housingApp = HousingApplication::getApplicationByUser($student->getUsername(), $term);
        if(!$housingApp instanceof LotteryApplication){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You are not eligible to re-apply for a Residential Learning Community.');
            $errorCmd->redirect();
        }

        # Make sure that the student has not already applied for this term
        $rlcApp = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $term);
        if(!is_null($rlcApp)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You have already re-applied for a Residential Learning Community for this term.');
            $errorCmd->redirect();
        }

        # Look up any existing RLC assignment (for the current term, should be the Spring term)
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), Term::getPrevTerm(Term::getCurrentTerm()));

        # Get the list of RLCs that the student is eligible for
        # Note: hard coded to 'C' because we know they're continuing at this point.
        # This accounts for freshmen addmitted in the spring, who will still have the 'F' type.
        $communities = HMS_Learning_Community::getRLCListReapplication(false, 'C');

        $view = new RlcReapplicationView($student, $term, $rlcAssignment, $communities);

        $context->setContent($view->show());
    }
}

?>