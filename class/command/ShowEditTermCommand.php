<?php

class ShowEditTermCommand extends Command {

    function getRequestVars() {
        $vars = array('action' => 'ShowEditTerm');

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'edit_terms')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit terms.');
        }
         
        PHPWS_Core::initModClass('hms', 'TermEditView.php');

        $termView = new TermEditView(Term::getSelectedTerm());
        $context->setContent($termView->show());
    }
}

?>