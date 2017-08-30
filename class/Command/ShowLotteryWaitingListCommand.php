<?php

namespace Homestead\Command;

use \Homestead\LotteryWaitingListView;
use \Homestead\Exception\PermissionException;

class ShowLotteryWaitingListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowLotteryWaitingList');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to add lottery entries.');
        }

        $view = new LotteryWaitingListView();
        $context->setContent($view->show());
    }
}
