<?php

namespace Homestead\command;

use \Homestead\Command;

class SubmitRLCReapplicationPageOneCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRLCReapplicationPageOne');
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
        // $menuCmd = CommandFactory::getCommand('ShowStudentMenu');

        // Pull in data for local use
        $rlcOpt        = $context->get('rlc_opt');
        $rlcChoice1    = $context->get('rlc_choice_1');
        $rlcChoice2    = $context->get('rlc_choice_2');
        $rlcChoice3    = $context->get('rlc_choice_3');
        $why           = $context->get('why_this_rlc');
        $contribute    = $context->get('contribute_gain');

        // Change any 'none's into null
        if($rlcChoice2 == 'none'){
            $rlcChoice2 = null;
        }
        if($rlcChoice3 == 'none'){
            $rlcChoice3 = null;
        }


        # Get the list of RLCs that the student is eligible for
        # Note: hard coded to 'C' because we know they're continuing at this point.
        # This accounts for freshmen addmitted in the spring, who will still have the 'F' type.
        $communities = HMS_Learning_Community::getRlcListReapplication(false, 'C');

        # Look up any existing RLC assignment (for the current term, should be the Spring term)
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), Term::getPrevTerm(Term::getCurrentTerm()));

        // Sanity checking on user-supplied data
        // If the student is already in an RLC, and the student is eligible to reapply for that RLC (RLC always takes returners,
        // or the RLC is in the list of communities this student is eligible for), then check to make the user chose something for the re-apply option.
        if(!is_null($rlcAssignment) && (array_key_exists($rlcAssignment->getRlcId(), $communities) || $rlcAssignment->getRlc()->getMembersReapply() == 1) && is_null($rlcOpt)){
            \NQ::simple('hms', NotificationView::ERROR, 'Please choose whether you would like to continue in your currnet RLC, or apply for a different community.');
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
                \NQ::simple('hms', NotificationView::ERROR, 'You must choose a community as your "first choice".');
                $formCmd->redirect();
            }

            if((isset($rlcChoice2) && $rlcChoice1 == $rlcChoice2) || (isset($rlcChoice2) && isset($rlcChoice3) && $rlcChoice2 == $rlcChoice3) || (isset($rlcChoice3) && $rlcChoice1 == $rlcChoice3)){
                \NQ::simple('hms', NotificationView::ERROR, 'You cannot choose the same community twice.');
                $formCmd->redirect();
            }
        }

        // Check the short answer questions
        if(empty($why) || empty($contribute)){
            \NQ::simple('hms', NotificationView::ERROR, 'Please respond to both of the short answer questions.');
            $formCmd->redirect();
        }

        $wordLimit = 500;
        if(str_word_count($why) > $wordLimit){
            \NQ::simple('hms', NotificationView::ERROR, 'Your answer to question number one is too long. Please limit your response to 500 words or less.');
            $formCmd->redirect();
        }

        $wordLimit = 500;
        if(str_word_count($contribute) > $wordLimit){
            \NQ::simple('hms', NotificationView::ERROR, 'Your answer to question number two is too long. Please limit your response to 500 words or less.');
            $formCmd->redirect();
        }

        $app = new HMS_RLC_Application();

        $app->setUsername($student->getUsername());
        $app->setFirstChoice($rlcChoice1);
        $app->setSecondChoice($rlcChoice2);
        $app->setThirdChoice($rlcChoice3);

        $app->setWhySpecificCommunities($why);
        $app->setStrengthsWeaknesses($contribute);

        $_SESSION['RLC_REAPP'] = $app;

        // Redirect to the page 2 view command
        $page2cmd = CommandFactory::getCommand('ShowRlcReapplicationPageTwo');
        $page2cmd->setTerm($term);
        $page2cmd->redirect();
    }

}
