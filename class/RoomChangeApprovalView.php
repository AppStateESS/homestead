<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeListView.php');

/**
 * Shows an overview of all pending and inactive requests
 * for a given RD/coordinator/admin's residents.
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeApprovalView extends hms\View {

    private $needsApproval;
    private $approved;
    private $allPending;
    private $completed;
    private $inactive;

    private $hallNames;
    private $term;

    public function __construct(Array $needsApprovalRequests, Array $approvedRequests, Array $allPendingRequests, Array $completedRequests, Array $inactiveRequests, Array $hallNames, $term)
    {
        $this->needsApproval = $needsApprovalRequests;
        $this->approved = $approvedRequests;
        $this->allPending = $allPendingRequests;
        $this->completed = $completedRequests;
        $this->inactive = $inactiveRequests;

        $this->hallNames = $hallNames;
        $this->term = $term;
    }

    public function show()
    {
        $tpl = array();

        $tpl['HALL_NAMES'] = implode(', ', $this->hallNames);

        $needsActionList = new RoomChangeListView($this->needsApproval, $this->term);
        $tpl['NEEDS_ACTION'] = $needsActionList->show();

        $approvedList = new RoomChangeListView($this->approved, $this->term);
        $tpl['APPROVED'] = $approvedList->show();

        $pendingList = new RoomChangeListView($this->allPending, $this->term);
        $tpl['PENDING'] = $pendingList->show();

        $completedList = new RoomChangeListView($this->completed, $this->term);
        $tpl['COMPLETED'] = $completedList->show();

        $inactiveList = new RoomChangeListView($this->inactive, $this->term);
        $tpl['INACTIVE'] = $inactiveList->show();

        return PHPWS_Template::process($tpl, 'hms', 'admin/RoomChangeApprovalView.tpl');
    }
}

?>
