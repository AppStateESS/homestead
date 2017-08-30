<?php

namespace Homestead\Command;

use \Homestead\LotterySettingsFormView;
use \Homestead\Exception\PermissionException;

class ShowLotterySettingsCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowLotterySettings');
    }

    public function execute(CommandContext $context)
    {

        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to change lottery settings.');
        }

        $view = new LotterySettingsFormView();
        $context->setContent($view->show());
    }
}
