<?php

namespace Homestead\Command;

use \Homestead\LotteryApplication;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

  /**
   * RemoveSpecialInterest
   *
   * Un-accept a student from a special interest group.
   *
   * @author Robert Bost <bostrt at tux dot appstate dot edu>
   */

class RemoveSpecialInterestCommand extends Command
{
    private $group;
    private $id;

    public function getRequestVars()
    {
        return array('action' => 'RemoveSpecialInterest',
                     'group' => $this->group,
                     'id' => $this->id);
    }

    public function execute(CommandContext $context)
    {
        // Check permissions
        if(!\Current_User::allow('hms', 'special_interest_approval')){
            throw new PermissionException('You do not have permission to approve special interest group requests.');
        }

        if(is_null($context->get('id'))){
            throw new \InvalidArgumentException('Missing application id.');
        }

        $app = new LotteryApplication($context->get('id'));

        $app->special_interest = null;

        $app->save();

        \NQ::simple('hms', NotificationView::SUCCESS, "Removed {$app->getUsername()}");

        $cmd = CommandFactory::getCommand('ShowSpecialInterestGroupApproval');
        $cmd->setGroup($context->get('group'));
        $cmd->redirect();
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    public function setGroup($group)
    {
        $this->group = $group;
    }

}
