<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\HMS_Permission;
use \Homestead\ResidenceHall;
use \Homestead\Floor;
use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\RoomChangeRequestFactory;
use \Homestead\RoomChangeApprovalView;
use \Homestead\NotificationView;

class ShowRDRoomChangeListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowRDRoomChangeList');
    }

    public function execute(CommandContext $context){

        $term = Term::getCurrentTerm();

        // Get the list of role memberships this user has
        // NB: This gets memberships for all terms.. must filter later
        $hms_perm = new HMS_Permission();
        $memberships = $hms_perm->getMembership('room_change_approve', NULL, UserStatus::getUsername());



        // Use the roles to instantiate a list of floors this user has access to
        $floors = array();

        foreach ($memberships as $member) {
            if ($member['class'] == 'residencehall') {
                $hall = new ResidenceHall($member['instance']);

                // Filter out halls that aren't in the current term
                if($hall->getTerm() != $term) {
                    continue;
                }

                $floors = array_merge($floors, $hall->getFloors());

            } else if ($member['class'] == 'floor') {
                $f = new Floor($member['instance']);

                // Filter out floors that aren't in the current term
                if($f->getTerm() != $term) {
                    continue;
                }

                $floors[] = $f;

            } else {
                throw new \Exception('Unknown object type.');
            }
        }

        if(empty($floors)){
            \NQ::simple('hms', NotificationView::ERROR, "You do not have the 'RD' role on any residence halls or floors.");
            $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
            $cmd->redirect();
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

        $approvedChanges = RoomChangeRequestFactory::getRoomChangesByFloor($term, $uniqueFloors, array('Approved'));

        $allPendingChanges = RoomChangeRequestFactory::getRoomChangesByFloor($term, $uniqueFloors, array('Pending', 'Hold'));

        $completedChanges = RoomChangeRequestFactory::getRoomChangesByFloor($term, $uniqueFloors, array('Complete'));

        $inactiveChanges = RoomChangeRequestFactory::getRoomChangesByFloor($term, $uniqueFloors, array('Cancelled', 'Denied'));


        $view = new RoomChangeApprovalView($needsApprovalChanges, $approvedChanges, $allPendingChanges, $completedChanges, $inactiveChanges, $hallNames, $term);

        $context->setContent($view->show());
    }
}
