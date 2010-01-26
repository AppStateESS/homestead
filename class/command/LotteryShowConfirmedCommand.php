<?php

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
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'LotteryChooseRoomThanksView.php');
        
        $roomId = $context->get('roomId');
        
        $room = new HMS_Room($roomId);
        
        $view = new LotteryChooseRoomThanksView($room);
        
        $context->setContent($view->show());
    }
}

?>