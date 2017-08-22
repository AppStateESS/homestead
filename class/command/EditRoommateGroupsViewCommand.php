<?php

namespace Homestead\command;

use \Homestead\Command;

class EditRoommateGroupsViewCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'=>'EditRoommateGroupsView');


        return $vars;
    }

    public function execute(CommandContext $context)
    {

        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'roommate_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to create/edit roommate groups.');
        }

        PHPWS_Core::initModClass('hms', 'EditRoommateGroupsView.php');

        $editRoommateView = new EditRoommateGroupsView();
        $context->setContent($editRoommateView->show());
    }
}
