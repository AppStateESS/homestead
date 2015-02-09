<?php

class LotteryShowChooseHalLCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'LotteryShowChooseHall');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'LotteryChooseHallView.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'LotteryProcess.php');
        PHPWS_Core::initModClass('hms', 'RlcMembershipFactory.php');
        
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        
        // Check the hard cap!
        if(LotteryProcess::hardCapReached($term)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, re-application is now closed.');
            $errorCmd = CommandFactory::getCommand('ShowStudentMenu');
            $errorCmd->redirect();
        }
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $rlcAssignment = RlcMembershipFactory::getMembership($student, $term);
        
        if($rlcAssignment == false) {
        	$rlcAssignment = null;
        }
        
        $view = new LotteryChooseHallView($student, $term, $rlcAssignment);
        
        $context->setContent($view->show());
    }
}

?>