<?php

namespace Homestead\Command;

use \Homestead\LotteryAdminEntryView;
use \Homestead\Exception\PermissionException;

class ShowLotteryAdminEntryCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowLotteryAdminEntry');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to add lottery entries.');
        }

        $view = new LotteryAdminEntryView();

        $context->setContent($view->show());
    }
}
