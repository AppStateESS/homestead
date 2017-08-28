<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initModClass('hms', 'AdminAddRlcMemberView.php');

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
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
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
