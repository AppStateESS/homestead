<?php

namespace Homestead\Command;

use \Homestead\SendRlcInvitesView;

class ShowSendRlcInvitesCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowSendRlcInvites');
    }

    public function execute(CommandContext $context)
    {
        $view = new SendRlcInvitesView();
        $context->setContent($view->show());
    }
}
