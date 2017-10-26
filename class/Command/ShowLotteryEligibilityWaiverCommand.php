<?php

namespace Homestead\Command;

use \Homestead\LotteryEligibilityWaiverView;
use \Homestead\Exception\PermissionException;

class ShowLotteryEligibilityWaiverCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowLotteryEligibilityWaiver');
    }

    public function execute(CommandContext $context){

        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to add lottery entries.');
        }

        $view = new LotteryEligibilityWaiverView();
        $context->setContent($view->show());
    }
}
