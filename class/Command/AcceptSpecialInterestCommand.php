<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;
use \Homestead\LotteryApplication;

class AcceptSpecialInterestCommand extends Command {

    private $group;
    private $id;

    public function setGroup($group)
    {
        $this->group = $group;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getRequestVars()
    {
        $requestVars = array('action' => 'AcceptSpecialInterest',
                             'group'  => $this->group,
                             'id'     => $this->id);

        return $requestVars;
    }

    public function execute(CommandContext $context)
    {
        # Check permissions
        if(!\Current_User::allow('hms', 'special_interest_approval')){
            throw new PermissionException('You do not have permission to approve special interest group requests.');
        }

        if(is_null($context->get('id'))){
            throw new \InvalidArgumentException('Missing application id.');
        }

        $app = new LotteryApplication($context->get('id'));
        $app->special_interest = $context->get('group');

        $app->save();

        \NQ::simple('hms', NotificationView::SUCCESS, "Accepted {$app->getUsername()}");

        $cmd = CommandFactory::getCommand('ShowSpecialInterestGroupApproval');
        $cmd->setGroup($context->get('group'));
        $cmd->redirect();
    }
}
