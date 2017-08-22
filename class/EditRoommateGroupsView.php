<?php

namespace Homestead;

PHPWS_Core::initModClass('hms', 'ConfirmedRoommatePager.php');

/**
 * The UI for viewing and deleting confirmed roommate groups
 */

class EditRoommateGroupsView extends View {

    private $roommatePager;

    public function __construct(){
        $this->roommatePager = new ConfirmedRoommatePager();
    }

    public function show()
    {
        $tpl = array();

        $tpl['PAGER']       = $this->roommatePager->show();
        $tpl['TERM']        = Term::getPrintableSelectedTerm();

        $createCmd = CommandFactory::getCommand('CreateRoommateGroupView');
        $tpl['CREATE_REQUEST_URI'] = $createCmd->getURI();

        Layout::addPageTitle("Edit Roommate Group");

        return PHPWS_Template::process($tpl, 'hms', 'admin/show_confirmed_roommates.tpl');
    }
}
