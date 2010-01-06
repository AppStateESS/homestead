<?php

class EditRoommateGroupsViewCommand extends Command {

    function getRequestVars(){
        $vars = array('action'=>'EditRoommateGroupsView');


        return $vars;
    }

    function execute(CommandContext $context)
    {

        if(!Current_User::allow('hms', 'roommate_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to create/edit roommate groups.');
        }

        PHPWS_Core::initModClass('hms', 'EditRoommateGroupsView.php');

        $editRoommateView = new EditRoommateGroupsView();
        $context->setContent($editRoommateView->show());
    }
}

?>
