<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeApprovalView.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

class ShowRDRoomChangeListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowRDRoomChangeList');
    }

    public function execute(CommandContext $context){

        $term = Term::getCurrentTerm();

        // Get the list of role memberships this user has
        // TODO For which term? This gets memberships for all terms?
        $memberships = HMS_Permission::getMembership('room_change_approve', NULL, UserStatus::getUsername());

        if(empty($memberships)){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("You do not have the 'RD' role on any residence halls or floors.");
        }

        // Use the roles to instantiate a list of floors this user has access to
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
        $uniqueFloors = array();
        foreach($floors as $floor){
            $uniqueFloors[$floor->getId()] = $floor;
        }

        // Use the list of floors to get a unique list of hall names
        $hallNames = array();
        foreach($uniqueFloors as $floor){
            $hall = $floor->get_parent();
            $hallNames[$hall->getId()] = $hall->getHallName();
        }

        // Get the set of room changes which are not complete based on the floor list
        $needsApprovalChanges = RoomChangeRequestFactory::getRoomChangesNeedsApproval($term, $uniqueFloors);

        $allPendingChanges = RoomChangeRequestFactory::getRoomChangesByFloor($term, $uniqueFloors, array('Pending', 'Hold'));

        $inactiveChanges = RoomChangeRequestFactory::getRoomChangesByFloor($term, $uniqueFloors, array('Cancelled', 'Denied'));


        $view = new RoomChangeApprovalView($needsApprovalChanges, $allPendingChanges, $inactiveChanges, $hallNames, $term);

        $context->setContent($view->show());
    }
}
?>