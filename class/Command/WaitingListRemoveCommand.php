<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\HousingApplicationFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;
use \Homestead\StudentFactory;

class WaitingListRemoveCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'WaitingListRemove');
    }

    public function execute(CommandContext $context){

        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have remove students from the waiting list.');
        }

        $username = $context->get('username');
        $cmd      = CommandFactory::getCommand('ShowLotteryWaitingList');

        if(!is_null($username)){
            $term = \PHPWS_Settings::get('hms', 'lottery_term');
            $student = StudentFactory::getStudentByUsername($username, $term);
            $app = HousingApplicationFactory::getAppByStudent($student, $term);
            $app->waiting_list_hide = 1;
            $result = $app->save();

            if(!\PHPWS_Error::logIfError($result)){
                \NQ::simple('hms', NotificationView::SUCCESS, "$username removed from the waiting list!");
                $cmd->redirect();
            }
        }
        \NQ::simple('hms', NotificationView::SUCCESS, "Unable to remove $username from the waiting list!");
        $cmd->redirect();
    }
}
