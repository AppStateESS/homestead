<?php

namespace Homestead\Command;

use \Homestead\SendLotteryInvitesView;

class ShowSendLotteryInvitesCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowSendLotteryInvites');
    }

    public function execute(CommandContext $context)
    {
        $view = new SendLotteryInvitesView();

        $context->setContent($view->show());
    }
}
