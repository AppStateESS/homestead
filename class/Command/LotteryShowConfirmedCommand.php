<?php

namespace Homestead\Command;

use \Homestead\LotteryChooseRoomThanksView;
use \Homestead\Room;

class LotteryShowConfirmedCommand extends Command {

    private $roomId;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function getRequestVars(){
        $vars = array('action'=>'LotteryShowConfirmed');

        $vars['roomId'] = $this->roomId;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $roomId = $context->get('roomId');

        $room = new Room($roomId);

        $view = new LotteryChooseRoomThanksView($room);

        $context->setContent($view->show());
    }
}
