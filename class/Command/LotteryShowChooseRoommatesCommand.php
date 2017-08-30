<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\LotteryChooseRoommatesView;

class LotteryShowChooseRoommatesCommand extends Command {

    private $roomId;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'LotteryShowChooseRoommates', 'roomId'=>$this->roomId);
    }

    public function execute(CommandContext $context)
    {
        $term = \PHPWS_Settings::get('hms', 'lottery_term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $roomId = $context->get('roomId');

        if(!isset($roomId) || is_null($roomId) || empty($roomId)){
            throw new \InvalidArgumentException('Missing room id.');
        }

        $view = new LotteryChooseRoommatesView($student, $term, $roomId);

        $context->setContent($view->show());
    }
}
