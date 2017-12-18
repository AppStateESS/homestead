<?php

namespace Homestead\Command;

use \Homestead\EditRlcView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class ShowCommunitiesCommand extends Command {
    public function getRequestVars(){
        return array('action' => 'ShowCommunities');
    }

    public function execute(CommandContext $context){

        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'learning_community_maintenance')) {
            throw new PermissionException('You do not have permission to edit RLCs.');
        }

        $view = new EditRlcView();

        $context->setContent($view->show());
    }
}
