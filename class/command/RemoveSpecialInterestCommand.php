<?php

  /**
   * RemoveSpecialInterest
   *
   * Un-accept a student from a special interest group.
   *
   * @author Robert Bost <bostrt at tux dot appstate dot edu>
   */


PHPWS_Core::initModClass('hms', 'Command.php');

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
        if(!Current_User::allow('hms', 'special_interest_approval')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve special interest group requests.');
        }

        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

        if(is_null($context->get('id'))){
            throw new InvalidArgumentException('Missing application id.');
        }

        $app = new LotteryApplication($context->get('id'));

        $app->special_interest = null;

        $app->save();

        NQ::simple('hms', hms\NotificationView::SUCCESS, "Removed {$app->getUsername()}");

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
