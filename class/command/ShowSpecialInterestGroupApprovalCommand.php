<?php

PHPWS_Core::initModClass('hms', 'SpecialInterestGroupView.php');

class ShowSpecialInterestGroupApprovalCommand extends Command {

    public function getRequestVars()
    {
        $requestVars = array('action'=>'ShowSpecialInterestGroupApproval');

        return $requestVars;
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'special_interest_approval')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approval special interest groups.');
        }

        $view = new SpecialInterestGroupView($context->get('group'));
        $context->setContent($view->show());
    }
}
?>
