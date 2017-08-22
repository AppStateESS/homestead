<?php

namespace Homestead\command;

use \Homestead\Command;

class ShowSendLotteryInvitesCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowSendLotteryInvites');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'SendLotteryInvitesView.php');
        $view = new SendLotteryInvitesView();

        $context->setContent($view->show());
    }
}
