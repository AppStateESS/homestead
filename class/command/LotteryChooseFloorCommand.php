<?php

class LotteryChooseFloorCommand extends Command {
    
    private $floorId;
    
    public function setFloorId($id){
        $this->floorId = $id;
    }
    
    public function getRequestVars()
    {
        return array('action'=>'LotteryChooseFloor', 'floorId'=>$this->floorId);
    }
    
    public function execute(CommandContext $context)
    {
        $roomCmd = CommandFactory::getCommand('LotteryShowChooseRoom');
        $roomCmd->setFloorId($context->get('floorId'));
        $roomCmd->redirect();
    }
}

