<?php

namespace Homestead\command;

use \Homestead\Command;

class DeleteRoommateGroupCommand extends Command {

    private $id;

    public function setId($id){
        $this->id = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'DeleteRoommateGroup', 'id'=>$this->id);
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'roommate_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to create/edit roommate groups.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');

        $id = $context->get('id');

        if(is_null($id)){
            throw new \InvalidArgumentException('Missing roommate group id.');
        }

        $viewCmd = CommandFactory::getCommand('EditRoommateGroupsView');

        try{
            $roommate = new HMS_Roommate($id);
            $roommate->delete();
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::SUCCESS, 'Error deleting roommate group: ' . $e->getMessage());
            $viewCmd->redirect();
        }

        // Log the success
        $notes = "{$roommate->getRequestor()} requested {$roommate->getRequestee()}";
        HMS_Activity_Log::log_activity($roommate->getRequestor(), ACTIVITY_ADMIN_REMOVED_ROOMMATE, UserStatus::getUsername(), $notes);
        HMS_Activity_Log::log_activity($roommate->getRequestee(), ACTIVITY_ADMIN_REMOVED_ROOMMATE, UserStatus::getUsername(), $notes);

        \NQ::simple('hms', NotificationView::SUCCESS, 'Roommate group successfully deleted.');
        $viewCmd->redirect();
    }
}
