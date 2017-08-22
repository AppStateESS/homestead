<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'SpecialInterestGroupView.php');

class ShowSpecialInterestGroupApprovalCommand extends Command {

    private $group;

    public function setGroup($group){
        $this->group = $group;
    }

    public function getRequestVars()
    {
        $requestVars = array('action'=>'ShowSpecialInterestGroupApproval');

        if(isset($this->group)){
            $requestVars['group'] = $this->group;
        }

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
