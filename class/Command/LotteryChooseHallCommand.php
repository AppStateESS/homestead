<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;

class LotteryChooseHallCommand extends Command {

    private $hallId;

    public function setHallId($id){
        $this->hallId = $id;
    }

    public function getRequestVars(){
        $vars = array('action'=>'LotteryChooseHall', 'hallId'=>$this->hallId);

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $cmd = CommandFactory::getCommand('LotteryShowChooseFloor');
        $cmd->setHallId($context->get('hallId'));
        $cmd->redirect();
    }
}
