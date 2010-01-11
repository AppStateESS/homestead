<?php

class ShowLotteryAdminEntryCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowLotteryAdminEntry');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add lottery entries.');
        }
         
        PHPWS_Core::initModClass('hms', 'LotteryAdminEntryView.php');
        $view = new LotteryAdminEntryView();

        $context->setContent($view->show());
    }
}
?>
