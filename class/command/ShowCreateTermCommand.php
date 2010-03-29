<?php

class ShowCreateTermCommand extends Command {

    function getRequestVars() {
        $vars = array('action' => 'ShowCreateTerm');

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'edit_terms')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit terms.');
        }

        PHPWS_Core::initModClass('hms', 'CreateTermView.php');
        $view = new CreateTermView();
        $context->setContent($view->show());
    }
}

?>