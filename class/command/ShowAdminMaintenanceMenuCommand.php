<?php

class ShowAdminMaintenanceMenuCommand extends Command {

    function getRequestVars(){
        return array('action'=>'ShowAdminMaintenanceMenu');
    }

    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'AdminMaintenanceMenuView.php');

        if(!Current_User::isUnrestricted('hms')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view the admin maintenance menu.');
        }

        $adminMenu = new AdminMaintenanceMenuView();

        $context->setContent($adminMenu->show());
    }
}

?>
