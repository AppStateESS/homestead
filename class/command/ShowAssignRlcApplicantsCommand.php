<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentView.php');

class ShowAssignRlcApplicantsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowAssignRlcApplicants');

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
