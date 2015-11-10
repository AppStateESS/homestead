<?php

class ShowAdminMaintenanceMenuCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowAdminMaintenanceMenu');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::isUnrestricted('hms')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view the admin maintenance menu.');
        }

        PHPWS_Core::initModClass('hms', 'AdminMaintenanceMenuView.php');

        $adminMenu = new AdminMaintenanceMenuView();

        $context->setContent($adminMenu->show());
    }
}
