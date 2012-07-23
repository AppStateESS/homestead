<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentView.php');

/**
 * Command/controller for showing the view where an admin can assign students to RLCs.
 * 
 * @author jbooker
 * @package HMS
 */
class ShowAssignRlcApplicantsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowAssignRlcApplicants');

        return $vars;
    }

    public function execute(CommandContext $context){
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'view_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC applications.');
        }

        $view = new RlcAssignmentView($context->get('rlc'));
        $context->setContent($view->show());
    }
}
?>
