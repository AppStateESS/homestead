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
        $context->setContent('Success: room ' . $context->get('roomId'));
    }
}

?>