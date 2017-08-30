<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\RlcMembershipFactory;
use \Homestead\LotteryChooseRoomView;

class LotteryShowChooseRoomCommand extends Command {

    private $floorId;

    public function setFloorId($id){
        $this->floorId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'LotteryShowChooseRoom', 'floorId'=>$this->floorId);
        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $floorId = $context->get('floorId');

        if(!isset($floorId) || is_null($floorId) || empty($floorId)){
            throw new \InvalidArgumentException('Missing hall id.');
        }

        $term = \PHPWS_Settings::get('hms', 'lottery_term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $rlcAssignment = RlcMembershipFactory::getMembership($student, $term);

        if($rlcAssignment == false) {
        	$rlcAssignment = null;
        }

        $view = new LotteryChooseRoomView($student, $term, $floorId, $rlcAssignment);

        $context->setContent($view->show());
    }
}
