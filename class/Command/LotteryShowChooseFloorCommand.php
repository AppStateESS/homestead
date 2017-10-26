<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\RlcMembershipFactory;
use \Homestead\UserStatus;
use \Homestead\LotteryChooseFloorView;

class LotteryShowChooseFloorCommand extends Command {

    private $hallId;

    public function setHallId($id){
        $this->hallId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'LotteryShowChooseFloor', 'hallId'=>$this->hallId);
        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $hallId = $context->get('hallId');

        if(!isset($hallId) || is_null($hallId) || empty($hallId)){
            throw new \InvalidArgumentException('Missing hall id.');
        }

        $term = \PHPWS_Settings::get('hms', 'lottery_term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $rlcAssignment = RlcMembershipFactory::getMembership($student, $term);

        if($rlcAssignment == false) {
        	$rlcAssignment = null;
        }

        $view = new LotteryChooseFloorView($student, $term, $hallId, $rlcAssignment);

        $context->setContent($view->show());
    }
}
