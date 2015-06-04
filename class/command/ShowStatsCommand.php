<?php

class ShowStatsCommand extends Command {
    
    public function getRequestVars(){
        return array('action'=>'ShowStats');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'stats')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You are not allowed to view stats.');
        }
        PHPWS_Core::initModClass('hms', 'StatsView.php');

        $view = new StatsView();

        $context->setContent($view->show());
    }
}


