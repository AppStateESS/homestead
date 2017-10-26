<?php

namespace Homestead\Command;

use \Homestead\StatsView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class ShowStatsCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowStats');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'stats')){
            throw new PermissionException('You are not allowed to view stats.');
        }

        $view = new StatsView();

        $context->setContent($view->show());
    }
}
