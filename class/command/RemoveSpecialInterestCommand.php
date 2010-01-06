<?php

class RemoveSpecialInterestCommand extends Command {
    protected $asuUsername;
    protected $group;

    public function getAsuUsername()
    {
        return $this->asuUsername;
    }

    public function setAsuUsername($username)
    {
        $this->asuUsername = $username;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    public function getRequestVars()
    {
        $requestVars = array('action' => 'RemoveSpecialInterest',
                             'group'  => $this->group);

        return $requestVars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

        # Check permissions
        if(!Current_User::allow('hms', 'special_interest_approval')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve special interest group requests.');
        }

        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

        if( !is_null($context->get('id')) ){
            $app = new LotteryApplication($context->get('id'));
            $app->special_interest = NULL;
            $result = $app->save();

            if(PEAR::isError($result)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Error removing {$this->asuUsername}");
            }else{
                NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "Removed {$this->asuUsername}");
            }
        } else {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'No id provided to remove!');
        }

        $cmd = CommandFactory::getCommand('ShowSpecialInterestGroupApproval');
        $cmd->redirect();
    }
}
?>
