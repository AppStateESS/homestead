<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\RlcMembershipFactory;
use \Homestead\LotteryChooseHallView;

class LotteryShowChooseHalLCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'LotteryShowChooseHall');
    }

    public function execute(CommandContext $context)
    {
        $term = \PHPWS_Settings::get('hms', 'lottery_term');

        // Check the hard cap!
        if(LotteryProcess::hardCapReached($term)){
            \NQ::simple('hms', NotificationView::ERROR, 'Sorry, re-application is now closed.');
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
