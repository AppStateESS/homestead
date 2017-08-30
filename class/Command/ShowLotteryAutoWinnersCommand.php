<?php

namespace Homestead\Command;

use \Homestead\LotteryAutoWinnersView;
use \Homestead\Exception\PermissionException;

class ShowLotteryAutoWinnersCommand extends Command{

    public function getRequestVars()
    {
        return array('action'=>'ShowLotteryAutoWinners');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to add lottery entries.');
        }

        $view = new LotteryAutoWinnersView();
        $context->setContent($view->show());
    }
}
