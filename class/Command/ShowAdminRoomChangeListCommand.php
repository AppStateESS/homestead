<?php

namespace Homestead\Command;

 

class ShowAdminRoomChangeListCommand extends Command {

    public function getRequestVars() {
        return array('action' => 'ShowAdminRoomChangeList');
    }

    public function execute(CommandContext $context)
    {
        if (!\Current_User::allow('hms', 'admin_approve_room_change')) {
            PHPWS_Core::initModClasS('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve room changes.');
        }

        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeApprovalView.php');

        $term = Term::getSelectedTerm();

        // Get all requests in the FutureRDApproved state (i.e. waiting on housing assignments office)
        $needsApprovalChanges = RoomChangeRequestFactory::getAllRoomChangesNeedsAdminApproval($term);

        // Get all requests that are Approved (in-progress)
        $allApproved = RoomChangeRequestFactory::getAllRoomChangesByState($term, array('Approved'));

        // Get all requests that are pending/in-progress, but not waiting on Housing
        $allPending = RoomChangeRequestFactory::getAllRoomChangesByState($term, array('Pending', 'Hold'));

        // Get all complete requests
        $allComplete = RoomChangeRequestFactory::getAllRoomChangesByState($term, array('Complete'));

        // Get all requests that are inactive (cancelled, denied, complete)
        $allInactive = RoomChangeRequestFactory::getAllRoomChangesByState($term, array('Cancelled', 'Denied'));

        $view = new RoomChangeApprovalView($needsApprovalChanges, $allApproved, $allPending, $allComplete, $allInactive, array('All Halls'), $term);

        $context->setContent($view->show());
    }

}
