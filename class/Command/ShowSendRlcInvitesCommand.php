<?php

namespace Homestead\Command;

 

class ShowSendRlcInvitesCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowSendRlcInvites');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'SendRlcInvitesView.php');

        $view = new SendRlcInvitesView();
        $context->setContent($view->show());
    }
}
