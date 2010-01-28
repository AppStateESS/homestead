<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationReView.php');

class ViewRlcApplicationCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'ViewRlcApplication');
    }

    public function execute(CommandContext $context){
        if(!Current_User::allow('hms', 'view_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC applications.');
        }

        $view = new RlcApplicationReView($context);
        $context->setContent($view->show());
    }
}

?>