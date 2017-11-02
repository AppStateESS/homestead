<?php

namespace Homestead\Command;

class ServiceDeskMenuCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ServiceDeskMenu');
    }

    public function execute(CommandContext $context)
    {
        $serviceDeskMenu = new \Homestead\ServiceDeskMenu();

        $context->setContent($serviceDeskMenu->show());
    }
}
