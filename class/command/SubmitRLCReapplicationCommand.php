<?php

class SubmitRLCReapplicationCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRLCReapplication');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');

        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Commands for re-directing later
        $formCmd = CommandFactory::getCommand('ShowRlcReapplication');
        $formCmd->setTerm($term);
        $menuCmd = CommandFactory::getCommand('ShowStudentMenu');

        // Double check the the student is eligible
        $housingApp = HousingApplication::getApplicationByUser($student->getUsername(), $term);
        if(!$housingApp instanceof LotteryApplication){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You are not eligible to re-apply for a Learning Community.');
            $menuCmd->redirect();
        }

        // Make sure the user doesn't already have an application on file for this term
        $app = HMS_RLC_Application::checkForApplication($student->getUsername(), $term);
        if($app !== FALSE){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'You have already re-applied for a Learning Community for that term.');
            $menuCmd->redirect();
        }

        # Look up any existing RLC assignment (for the current term, should be the Spring term)
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), Term::getPrevTerm(Term::getCurrentTerm()));

        # Get the list of RLCs that the student is eligible for
        # Note: hard coded to 'C' because we know they're continuing at this point.
        # This accounts for freshmen addmitted in the spring, who will still have the 'F' type.
        $communities = HMS_Learning_Community::getRLCListReapplication(false, 'C');

        // Pull in data for local use
        $rlcOpt        = $context->get('rlc_opt');
        $rlcChoice1    = $context->get('rlc_choice_1');
        $rlcChoice2    = $context->get('rlc_choice_2');
        $rlcChoice3    = $context->get('rlc_choice_3');
        $why           = $context->get('why_this_rlc');
        $contribute    = $context->get('contribute_gain');

        // Sanity checking on user-supplied data
        // If the student is already in an RLC, and the student is eligible to reapply for that RLC (RLC always takes returners,
        // or the RLC is in the list of communities this student is eligible for), then check to make the user chose something for the re-apply option.
        if(!is_null($rlcAssignment) && (array_key_exists($rlcAssignment->getRlcId(), $communities) || $rlcAssignment->getRlc()->getMembersReapply() == 1) && is_null($rlcOpt)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please choose whether you would like to continue in your currnet RLC, or apply for a different community.');
            $formCmd->redirect();
        }

        // If the user is 'contining' in his/her current RLC, then figure that out and set it
        if(!is_null($rlcOpt) && $rlcOpt == 'continue'){
            $rlcChoice1 = $rlcAssignment->getRLC()->get_id();
            $rlcChoice2 = NULL;
            $rlcChoice3 = NULL;
        }else{
            // User either can't 'continue' or didn't want to. Check that the user supplied rankings isstead.
            // Make sure a first choice was made
            if($rlcChoice1 == 'select'){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must choose a community as your "first choice".');
                $formCmd->redirect();
            }

            if(($rlcChoice2 != 'none' && $rlcChoice1 == $rlcChoice2) || ($rlcChoice2 != 'none' && $rlcChoice3 != 'none' && $rlcChoice2 == $rlcChoice3) || ($rlcChoice3 != 'none' && $rlcChoice1 == $rlcChoice3)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You cannot choose the same community twice.');
                $formCmd->redirect();
            }
        }

        // Check the short answer questions
        if(empty($why) || empty($contribute)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please respond to both of the short answer questions.');
            $formCmd->redirect();
        }

        test($_REQUEST,1);
    }

}

?>