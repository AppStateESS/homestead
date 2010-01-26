<?php

class LotteryShowConfirmedRoommateThanksCommand extends Command {

    private $requestId;

    public function setRequestId($id){
        $this->requestId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'LotteryShowConfirmedRoommateThanks', 'requestId'=>$this->requestId);
    }

    public function execute(CommandContext $context){

        PHPWS_Core::initModClass('hms', 'LotteryConfirmedRoommateThanksView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        
        $invite = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('requestId'));
        $bed = new HMS_Bed($invite['bed_id']);
        
        $view = new LotteryConfirmedRoommateThanksView($invite , $bed);
        $context->setContent($view->show());
    }
}