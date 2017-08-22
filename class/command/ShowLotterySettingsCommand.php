<?php

namespace Homestead\command;

use \Homestead\Command;

class ShowLotterySettingsCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowLotterySettings');
    }

    public function execute(CommandContext $context)
    {

        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to change lottery settings.');
        }

        PHPWS_Core::initModClass('hms', 'LotterySettingsFormView.php');

        $view = new LotterySettingsFormView();
        $context->setContent($view->show());
    }
}
