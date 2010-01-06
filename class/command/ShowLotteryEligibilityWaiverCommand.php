<?php

PHPWS_Core::initModClass('hms', 'LotteryEligibilityWaiverView.php');

class ShowLotteryEligibilityWaiverCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowLotteryEligibilityWaiver');
    }

    public function execute(CommandContext $context){
         
        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add lottery entries.');
        }
         
        $view = new LotteryEligibilityWaiverView();
        $context->setContent($view->show());
    }
}
?>
