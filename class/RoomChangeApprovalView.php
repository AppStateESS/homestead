<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeListView.php');

/**
 * Named poorly, but shows an overview of all pending and inactive requests
 * for a given RD/coordinator/admin's residents.
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeApprovalView extends View {

    private $roomChanges;
    private $hallNames;
    private $term;

    public function __construct(Array $roomChangeRequests, Array $hallNames, $term)
    {
        $this->roomChanges = $roomChangeRequests;
        $this->hallNames = $hallNames;
        $this->term = $term;
    }

    public function show()
    {
        $tpl = array();

        $tpl['HALL_NAMES'] = implode(', ', $this->hallNames);

        $needsActionList = new RoomChangeListView($this->roomChanges, $this->term);
        $tpl['NEEDS_ACTION'] = $needsActionList->show();


        $pendingList = new RoomChangeListView(array(), $this->term); //TODO
        $tpl['PENDING'] = $pendingList->show();

        $inactiveList = new RoomChangeListView(array(), $this->term); //TODO
        $tpl['INACTIVE'] = $inactiveList->show();

        return PHPWS_Template::process($tpl, 'hms', 'admin/RoomChangeApprovalView.tpl');
    }
}

?>