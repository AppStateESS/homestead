<?php

class LotteryChooseRoomCommand extends Command {
    
    private $roomId;
    
    public function setRoomId($id){
        $this->roomId = $id;
    }
    
    public function getRequestVars()
    {
        return array('action'=>'LotteryChooseRoom', 'roomId'=>$this->roomId);
    }
    
    public function execute(CommandContext $context)
    {
        $roomCmd = CommandFactory::getCommand('LotteryShowChooseRoommates');
        $roomCmd->setRoomId($context->get('roomId'));
        $roomCmd->redirect();
    }
}

?>