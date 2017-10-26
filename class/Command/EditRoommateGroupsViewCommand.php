<?php

namespace Homestead\Command;

use \Homestead\EditRoommateGroupsView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class EditRoommateGroupsViewCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'=>'EditRoommateGroupsView');


        return $vars;
    }

    public function execute(CommandContext $context)
    {

        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'roommate_maintenance')){
            throw new PermissionException('You do not have permission to create/edit roommate groups.');
        }

        $editRoommateView = new EditRoommateGroupsView();
        $context->setContent($editRoommateView->show());
    }
}
