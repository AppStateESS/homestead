<?php

namespace Homestead\Command;

use \Homestead\OpenWaitingListView;
use \Homestead\Exception\PermissionException;

class ShowOpenWaitingListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowOpenWaitingList');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to add lottery entries.');
        }

        $view = new OpenWaitingListView();
        $context->setContent($view->show());
    }

}
