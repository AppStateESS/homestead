<?php

namespace Homestead\Command;

class ReapplicationMenuCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ReapplicationMenu');
    }

    public function execute(CommandContext $context)
    {
        $reapplicationMenu = new \Homestead\ReapplicationMaintenanceMenu();
        
        $context->setContent($reapplicationMenu->show());
    }
}
