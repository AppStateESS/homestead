<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');
PHPWS_Core::initModClass('hms', 'WaitingListApplication.php');

class OpenWaitingListRemoveCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'OpenWaitingListRemove');
    }

    public function execute(CommandContext $context){

        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have remove students from the waiting list.');
        }

        $username = $context->get('username');
        $cmd      = CommandFactory::getCommand('ShowOpenWaitingList');

        if(!is_null($username)){
            $app = HousingApplication::getApplicationByUser($username, PHPWS_Settings::get('hms', 'lottery_term'));
            $app->waiting_list_hide = 1;
            $result = $app->save();

            if(!PHPWS_Error::logIfError($result)){
                NQ::simple('hms', hms\NotificationView::SUCCESS, "$username removed from the waiting list!");
                $cmd->redirect();
            }
        }
        NQ::simple('hms', hms\NotificationView::SUCCESS, "Unable to remove $username from the waiting list!");
        $cmd->redirect();
    }
}

?>
