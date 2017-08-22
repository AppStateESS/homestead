<?php

namespace Homestead\command;

use \Homestead\Command;

  /**
   * DenySpecialInterest
   *
   * Unset a student's preference for a special interest group
   * on their application.
   * A special needs preference includes pysch, physical, medical,
   * and gender.  If a student is denied special needs unset all of
   * these at once. Everything else is one-for-one right now.
   *
   * @author Robert Bost <bostrt at tux dot appstate dot edu>
   */

class DenySpecialInterestCommand extends Command
{
    private $id;
    private $group;

    public function getRequestVars()
    {
        return array('action' => 'DenySpecialInterest',
                     'id'     => $this->id,
                     'group'  => $this->group);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');

        // Check permissions
        if(!Current_User::allow('hms', 'special_interest_approval')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve special interest group requests.');
        }

        // Must have ID and group name set.
        if(is_null($context->get('id'))){
            throw new InvalidArgumentException('Missing application id.');
        }
        if(is_null($context->get('group'))){
            throw new InvalidArgumentException('Missing interest group name.');
        }

        // Load up application.
        $app = new LotteryApplication($context->get('id'));

        // Unset proper preference in student's application.
        switch($context->get('group')){
        case 'watauga_global':
            $app->setWataugaGlobalPref(0);
            break;
        case 'honors':
            $app->setHonorsPref(0);
            break;
        case 'teaching':
            $app->setTeachingFellowsPref(0);
            break;
        case 'sorority':
            $app->setSororityPref(0);
            break;
        }

        // Save, notify, and buh-bye
        $app->save();

        NQ::simple('hms', hms\NotificationView::SUCCESS, "Denied {$app->getUsername()}");

        $cmd = CommandFactory::getCommand('ShowSpecialInterestGroupApproval');
        $cmd->setGroup($context->get('group'));
        $cmd->redirect();
    }

    public function setId($id){
        $this->id = $id;
    }

    public function setGroup($group){
        $this->group = $group;
    }
}
