<?php

namespace Homestead\Command;

use \Homestead\HMS_Lottery;
use \Homestead\HMS_Bed;
use \Homestead\LotteryConfirmedRoommateThanksView;

class LotteryShowConfirmedRoommateThanksCommand extends Command {

    private $requestId;

    public function setRequestId($id){
        $this->requestId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'LotteryShowConfirmedRoommateThanks', 'requestId'=>$this->requestId);
    }

    public function execute(CommandContext $context){

        $invite = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('requestId'));
        $bed = new HMS_Bed($invite['bed_id']);

        $view = new LotteryConfirmedRoommateThanksView($invite , $bed);
        $context->setContent($view->show());
    }
}
