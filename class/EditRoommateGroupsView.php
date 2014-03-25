<?php

PHPWS_Core::initModClass('hms', 'ConfirmedRoommatePager.php');

/**
 * The UI for viewing and deleting confirmed roommate groups
 */

class EditRoommateGroupsView extends hms\View{

    private $roommatePager;

    public function __construct(){
        $this->roommatePager = new ConfirmedRoommatePager();
    }

    public function show()
    {
        $tpl = array();

        $tpl['PAGER']       = $this->roommatePager->show();
        $tpl['TERM']        = Term::getPrintableSelectedTerm();

        Layout::addPageTitle("Edit Roommate Group");

        return PHPWS_Template::process($tpl, 'hms', 'admin/show_confirmed_roommates.tpl');
    }
}

?>
