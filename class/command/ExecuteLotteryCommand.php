<?php

class ExecuteLotteryCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ExecuteLottery');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to administer re-application features.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        HMS_Lottery::runLottery();
        exit;
    }
}
