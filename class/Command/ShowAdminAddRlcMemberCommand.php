<?php

namespace Homestead\Command;

use \Homestead\HMS_Learning_Community;
use \Homestead\AdminAddRlcMemberView;
use \Homestead\Exception\PermissionException;

class ShowAdminAddRlcMemberCommand extends Command {

    private $community;

    public function getRequestVars()
    {
        return array(
                'action' => 'ShowAdminAddRlcMember',
                'communityId' => $this->community->getId()
        );
    }

    public function setCommunity(HMS_Learning_Community $community)
    {
        $this->community = $community;
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'add_rlc_members')){
            throw new PermissionException('You do not have permission to view RLC members.');
        }

        $communityId = $context->get('communityId');

        if(!isset($communityId) || $communityId == ''){
            throw new \InvalidArgumentException('Missing community id.');
        }

        $community = new HMS_Learning_Community($communityId);

        $view = new AdminAddRlcMemberView($community);

        $context->setContent($view->show());
    }
}
