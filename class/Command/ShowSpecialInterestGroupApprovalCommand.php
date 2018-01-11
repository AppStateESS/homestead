<?php

namespace Homestead\Command;

use \Homestead\SpecialInterestGroupView;
use \Homestead\Exception\PermissionException;

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
        if(!\Current_User::allow('hms', 'special_interest_approval')){
            throw new PermissionException('You do not have permission to approval special interest groups.');
        }

        $view = new SpecialInterestGroupView($context->get('group'));
        $context->setContent($view->show());
    }
}
