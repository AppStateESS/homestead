<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'LotteryWaitingListView.php');

class ShowLotteryWaitingListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowLotteryWaitingList');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add lottery entries.');
        }

        $view = new LotteryWaitingListView();
        $context->setContent($view->show());
    }
}
