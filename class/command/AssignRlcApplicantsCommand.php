<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentView.php');

class AssignRlcApplicantsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action'=>'AssignRlcApplicants');

        return $vars;
    }

    public function execute(CommandContext $context){

        if(!Current_User::allow('hms', 'view_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC applications.');
        }

        $view = new RlcAssignmentView();

        $context->setContent($view->show());
    }
}
?>
