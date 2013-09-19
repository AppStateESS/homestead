<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeApprovalListView.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

class ShowRDRoomChangeListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowRDRoomChangeList');
    }

    public function execute(CommandContext $context){

        // Get the list of role memberships this user has
        $memberships = HMS_Permission::getMembership('room_change_approve', NULL, UserStatus::getUsername());

        if(empty($memberships)){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("Your account does not have the 'RD' role on any residence halls or floors.");
        }

        // Use the rols to instantiate a list of floors this user has access to
        $floors = array();

        foreach ($memberships as $member) {
            if ($member['class'] == 'hms_residence_hall') {
                $hall = new HMS_Residence_Hall($member['instance']);
                $floors = array_merge($floors, $hall->getFloors());
            } else if ($member['class'] == 'hms_floor') {
                $floors[] = new HMS_Floor($member['instance']);
            } else {
                throw new Exception('Unknown object type.');
            }
        }


        // Remove duplicate floors
        //TODO

        test($floors,1);

        // Use the list of floors to get a unique list of hall names
        $hallNames = array();
        //TODO

        $view = new RoomChangeApprovalListView();
        $context->setContent($view->show());
    }
}
?>