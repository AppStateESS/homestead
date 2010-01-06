<?php

class ShowAdminMaintenanceMenuCommand extends Command {

    function getRequestVars(){
        return array('action'=>'ShowAdminMaintenanceMenu');
    }

    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'AdminMaintenanceMenuView.php');
        $adminMenu = new AdminMaintenanceMenuView();

        $context->setContent($adminMenu->show());
    }
}

?>
