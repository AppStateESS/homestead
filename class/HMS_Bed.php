<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
PHPWS_Core::initModClass('hms', 'HMS_Item.php');


class HMS_Bed extends HMS_Item {

    public $id;
    public $term;
    public $room_id = 0;
    public $bed_letter = null;
    public $banner_id = null;
    public $phone_number = null;
    public $bedroom_label = null;
    public $ra_roommate = null;
    public $international_reserved = 0;
    public $room_change_reserved = 0;
    public $_curr_assignment = null;

    public $persistent_id = null;
    public $ra = 0;

    /**
     * Holds the parent room object of this bed.
     */
    var $_room;

    public function HMS_Bed($id = 0)
    {
        $this->construct($id, 'hms_bed');
        // test($this);
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_bed');
    }

    public function copy($to_term, $room_id, $assignments)
    {
        if (!$this->id) {
            return false;
        }

        // echo "in hms_beds, making a copy of this bed<br>";

        $new_bed = clone ($this);
        $new_bed->reset();
        $new_bed->term = $to_term;
        $new_bed->room_id = $room_id;
        $new_bed->setRoomChangeReserved(0);

        try {
            $new_bed->save();
        } catch (Exception $e) {
            throw $e;
        }
        // Copy assignment
        if ($assignments) {
            // echo "loading assignments for this bed<br>";
            PHPWS_Core::initModClass('hms', 'HousingApplication.php');
            PHPWS_Core::initModClass('hms', 'Term.php');
            PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
            PHPWS_Core::initModClass('hms', 'StudentFactory.php');

            try {
                $this->loadAssignment();
            } catch (Exception $e) {
                throw $e;
            }

            if (isset($this->_curr_assignment)) {
                try {
                    try {
                        $student = StudentFactory::getStudentByUsername($this->_curr_assignment->asu_username, Term::getCurrentTerm());
                        $app = HousingApplication::getApplicationByUser($this->_curr_assignment->asu_username, Term::getCurrentTerm());
                    } catch (StudentNotFoundException $e) {
                        NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not copy assignment for ' . $this->_curr_assignment->asu_username);
                        return;
                    }
                    // meal option defaults to standard
                    $meal_option = BANNER_MEAL_STD;
                    if (!is_null($app)) {
                        $meal_option = $app->getMealPlan();
                    }
                    $note = "Assignment copied from " . Term::getPrintableCurrentTerm() . " to " . Term::toString($to_term);
                    HMS_Assignment::assignStudent($student, $to_term, null, $new_bed->id, $meal_option, $note, false, $this->_curr_assignment->getReason());
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }
    }

    public function get_banner_building_code()
    {
        $room = $this->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        return $building->banner_building_code;
    }

    public function get_row_tags()
    {
        $tpl = $this->item_tags();

        $tpl['BED_LETTER'] = $this->bed_letter;
        $tpl['BANNER_ID'] = $this->banner_id;
        $tpl['PHONE_NUMBER'] = $this->phone_number;

        return $tpl;
    }

    public function loadAssignment()
    {
        $assignment_found = false;
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('bed_id', $this->id);
        $db->addWhere('term', $this->term);
        $db->loadClass('hms', 'HMS_Assignment.php');
        $result = $db->getObjects('HMS_Assignment');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        } else if ($result == null) {
            return true;
        } elseif (sizeof($result) > 1) {
            PHPWS_Error::log(HMS_MULTIPLE_ASSIGNMENTS, 'hms', 'HMS_Bed::loadAssignment', "bedid : {$this->id}");
            return false;
        } else {
            $this->_curr_assignment = $result[0];
            return TRUE;
        }
    }

    public function loadRoom()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $result = new HMS_Room($this->room_id);
        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        $this->_room = & $result;
        return true;
    }

    public function get_parent()
    {
        if (!$this->loadRoom()) {
            return false;
        }

        return $this->_room;
    }

    public function get_number_of_assignees()
    {
        $this->loadAssignment();
        return (bool) $this->_curr_assignment ? 1 : 0;
    }

    public function get_assignee()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        if (!$this->loadAssignment()) {
            return false;
        }

        if (!isset($this->_curr_assignment->asu_username)) {
            return NULL;
        } else {
            return StudentFactory::getStudentByUsername($this->_curr_assignment->asu_username, $this->term);
        }
    }

    public function save()
    {
        $this->stamp();

        $db = new PHPWS_DB('hms_bed');
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function delete()
    {
        if (is_null($this->id) || !isset($this->id)) {
            throw new InvalidArgumentException('Invalid bed id.');
        }

        $db = new PHPWS_DB('hms_bed');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function has_vacancy()
    {
        if ($this->get_number_of_assignees() == 0) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Returns a string like "Justice Hall Room 110"
     */
    public function where_am_i($link = FALSE)
    {
        if (!$this->loadRoom()) {
            return null;
        }

        $room = $this->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        $text = $building->hall_name . ' Room ' . $room->room_number;

        if ($link) {
            $roomCmd = CommandFactory::getCommand('EditRoomView');
            $roomCmd->setRoomId($room->id);

            return $roomCmd->getLink($text);
        } else {
            return $text;
        }
    }

    public function getLink()
    {
        $bedCmd = CommandFactory::getCommand('EditBedView');
        $bedCmd->setBedId($this->id);
        return $bedCmd->getLink(strtoupper($this->bedroom_label) . $this->bed_letter);
    }

    /**
     * Returns a link.
     * If the bed is assigned, the link is to the
     * student info screen. Otherwise, the link is to the
     * assign student screen.
     */
    public function get_assigned_to_link($newWindow = FALSE)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $this->loadAssignment();

        if (isset($this->_curr_assignment)) {
            $link_re = '';
            $link_un = '';
            if (UserStatus::isAdmin() && Current_User::allow('hms', 'assignment_maintenance')) {
                $reAssignCmd = CommandFactory::getCommand('ShowAssignStudent');
                $reAssignCmd->setUsername($this->_curr_assignment->asu_username);
                $reAssignCmd->setBedId($this->id);
                $link_re = $reAssignCmd->getLink('(Re-assign)');

                $unAssignCmd = CommandFactory::getCommand('ShowUnassignStudent');
                $unAssignCmd->setUsername($this->_curr_assignment->asu_username);
                $link_un = $unAssignCmd->getLink('(Un-assign)');
            }

            try {
                $student = StudentFactory::getStudentByUsername($this->_curr_assignment->asu_username, Term::getSelectedTerm());
            } catch (StudentNotFoundException $e) {
                return 'Unknown student: ' . $this->_curr_assignment->getBannerId();
            }

            return $student->getProfileLink() . ' ' . $link_re . ' ' . $link_un;
        } else {
            $text = '&lt;unassigned&gt';
            if (UserStatus::isAdmin() && Current_User::allow('hms', 'assignment_maintenance')) {
                $assignCmd = CommandFactory::getCommand('ShowAssignStudent');
                $assignCmd->setBedId($this->id);
                if ($newWindow) {
                    return $assignCmd->getLink($text, 'index');
                } else {
                    return $assignCmd->getLink($text);
                }
            } else {
                return $text;
            }
        }
    }

    public function getPagerByRoomTags()
    {
        $tags['BEDROOM'] = $this->bedroom_label;
        $tags['BED_LETTER'] = $this->getLink();
        $tags['ASSIGNED_TO'] = $this->get_assigned_to_link();
        $tags['RA'] = $this->ra_roommate ? 'Yes' : 'No';

        $this->loadAssignment();
        if ($this->_curr_assignment == NULL && Current_User::allow('hms', 'bed_structure') && UserStatus::isAdmin()) {
            $deleteBedCmd = CommandFactory::getCommand('DeleteBed');
            $deleteBedCmd->setBedId($this->id);
            $deleteBedCmd->setRoomId($this->room_id);

            $confirm = array();
            $confirm['QUESTION'] = 'Are you sure want to delete bed ' . $this->bed_letter . '?';
            $confirm['ADDRESS'] = $deleteBedCmd->getURI();
            $confirm['LINK'] = 'Delete';
            $tags['DELETE'] = Layout::getJavascript('confirm', $confirm);
        }

        return $tags;
    }

    /**
     * Returns TRUE if the room can be administratively assigned, FALSE otherwise
     */
    public function canAssignHere()
    {
        // Make sure this bed isn't already assigned
        $this->loadAssignment();
        if ($this->_curr_assignment != NULL && $this->_curr_assignment > 0)
            return FALSE;

            // Get all of the parent objects
        $room = $this->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        // Check if everything is online
        if ($room->offline == 0)
            return FALSE;

        if ($floor->is_online == 1)
            return FALSE;

        if ($building->is_online == 1)
            return FALSE;

        return TRUE;
    }

    /**
     * Returns TRUE if the room can be auto-assigned
     */
    public function canAutoAssignHere()
    {
        // Make sure this bed isn't already assigned
        $this->loadAssignment();
        if ($this->_curr_assignment != NULL && $this->_curr_assignment > 0)
            return FALSE;

            // Get all of the parent objects
        $room = $this->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        // Check if everything is online
        if ($room->offline == 1)
            return FALSE;

        if ($floor->is_online == 0)
            return FALSE;

        if ($building->is_online == 0)
            return FALSE;

            // Make sure nothing is reserved
        if ($room->reserved == 1)
            return FALSE;

            // Make sure the room isn't a lobby
        if ($room->overflow == 1)
            return FALSE;

            // Make sure the room isn't private
        if ($room->private == 1)
            return FALSE;

            // Check if this bed is part of an RLC
        if ($floor->rlc_id != NULL)
            return FALSE;

        return TRUE;
    }

    public function is_lottery_reserved()
    {
        $db = new PHPWS_DB('hms_lottery_reservation');
        $db->addWhere('bed_id', $this->id);
        $db->addWhere('term', $this->term);
        $db->addWhere('expires_on', time(), '>');
        $result = $db->select('count');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if ($result > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function get_lottery_reservation_info()
    {
        $db = new PHPWS_DB('hms_lottery_reservation');
        $db->addWhere('bed_id', $this->id);
        $db->addWhere('term', $this->term);
        $db->addWhere('expires_on', time(), '>');
        $result = $db->select('row');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public function lottery_reserve($username, $requestor, $timestamp)
    {
        if ($this->is_lottery_reserved()) {
            return FALSE;
        }

        $db = new PHPWS_DB('hms_lottery_reservation');
        $db->addValue('asu_username', $username);
        $db->addValue('requestor', $requestor);
        $db->addValue('term', $this->term);
        $db->addValue('bed_id', $this->id);
        $db->addValue('expires_on', $timestamp);
        $result = $db->insert();

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        } else {
            return TRUE;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBannerId()
    {
        return $this->banner_id;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function isInternationalReserved()
    {
        return $this->international_reserved;
    }

    public function isRaRoommateReserved()
    {
        return $this->ra_roommate;
    }

    public function getRoomChangeReserved()
    {
        return $this->room_change_reserved;
    }

    public function isRoomChangeReserved()
    {
        if($this->room_change_reserved == 1){
            return true;
        }

        return false;
    }

    public function setRoomChangeReserved()
    {
        $this->room_change_reserved = 1;
    }

    public function clearRoomChangeReserved()
    {
        $this->room_change_reserved = 0;
    }

    public function getPersistentId()
    {
        return $this->persistent_id;
    }

    public function isRa()
    {
        if($this->ra == 1){
            return true;
        }

        return false;
    }

    public function setRa($raFlag)
    {
        $this->ra = $raFlag;
    }

    public function getBedroomLabel()
    {
        return $this->bedroom_label;
    }

    public function getLetter()
    {
        return $this->bed_letter;
    }

    /**
     * ****************
     * Static Methods *
     * ****************
     */

    /**
     * Returns an array of IDs of free beds (which can be auto_assigned)
     * Returns FALSE if there are no more free beds
     */
    public static function get_all_free_beds($term, $gender, $randomize = FALSE, $banner = FALSE)
    {
        $db = new PHPWS_DB('hms_bed');

        if ($banner) {
            $db->addColumn('hms_bed.banner_id');
            $db->addColumn('hms_residence_hall.banner_building_code');
        }
        $db->addColumn('id');

        // Only get free beds
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addWhere('hms_assignment.asu_username', NULL);

        // Join other tables so we can do the other 'assignable' checks
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        // Term
        $db->addWhere('hms_bed.term', $term);

        // Gender
        $db->addWhere('hms_room.gender_type', $gender);

        // Make sure everything is online
        $db->addWhere('hms_room.offline', 0);
        $db->addWhere('hms_floor.is_online', 1);
        $db->addWhere('hms_residence_hall.is_online', 1);

        // Make sure nothing is reserved
        $db->addWhere('hms_room.reserved', 0);
        // $db->addWhere('hms_room.is_medical', 0);

        // Don't get RA beds
        $db->addWhere('hms_room.ra', 0);

        // Don't get lobbies
        $db->addWhere('hms_room.overflow', 0);

        // Don't get private rooms
        $db->addWhere('hms_room.private', 0);

        // Don't get rooms on floors reserved for an RLC
        $db->addWhere('hms_floor.rlc_id', NULL);

        // Randomize if necessary
        if ($randomize) {
            $db->addOrder('random');
        }

        // $db->setTestMode();

        if ($banner) {
            $result = $db->select();
            if (PHPWS_Error::logIfError($result)) {
                throw new DatabaseException($result->toString());
            }

            return $result;
        }

        $result = $db->select('col');

        // In case of an error, log it and return it
        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        // Return FALSE if there were no results
        if (sizeof($result) <= 0) {
            return FALSE;
        }

        return $result;
    }

    /**
     * Returns the ID of a free bed (which can be auto-assigned)
     * Returns FALSE if there are no more free beds
     */
    public static function get_free_bed($term, $gender, $randomize = FALSE)
    {
        // Get the list of all free beds
        $beds = HMS_Bed::get_all_free_beds($term, $gender);

        // Check for db errors
        if (PEAR::isError($beds)) {
            return $beds;
        }

        // Check for no results (i.e. no empty beds), return false
        if ($beds == FALSE) {
            return FALSE;
        }

        if ($randomize) {
            // Get a random index between 0 and the max array index (size - 1)
            $random_index = mt_rand(0, sizeof($beds) - 1);
            return $beds[$random_index];
        } else {
            return $beds[0];
        }
    }

    /*
     * Adds a new bed to the specified room_id
    *
    * The 'ra_bed' flag is expected to be either TRUE or FALSE.
    * @return TRUE for success, FALSE otherwise
    */
    public static function addBed($roomId, $term, $bedLetter, $bedroomLabel, $phoneNumber, $bannerId, $raRoommate, $intlReserved, $raBed)
    {
        // Check permissions
        if (!UserStatus::isAdmin() || !Current_User::allow('hms', 'bed_structure')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add a bed.');
        }

        if ($raBed != 0 && $raBed != 1) {
            throw new InvalidArgumentException('Invalid RA bed flag.');
        }

        // Create a new bed object
        $bed = new HMS_Bed();

        $bed->room_id = $roomId;
        $bed->term = $term;
        $bed->bed_letter = $bedLetter;
        $bed->bedroom_label = $bedroomLabel;
        $bed->banner_id = $bannerId;
        $bed->phone_number = $phoneNumber;
        $bed->ra = $raBed;
        $bed->ra_roommate = $raRoommate;
        $bed->international_reserved = $intlReserved;

        try {
            $result = $bed->save();
        } catch (DatabaseException $e) {
            throw $e;
        }

        return true;
    }

    public static function deleteBed($bedId)
    {
        if (!UserStatus::isAdmin() || !Current_User::allow('hms', 'bed_structure')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to delete a bed.');
        }

        if (!isset($bedId)) {
            throw new InvalidArgumentException('Invalid bed id.');
        }

        // Create the bed object
        $bed = new HMS_Bed($bedId);

        // Make sure the bed isn't assigned to anyone
        $bed->loadAssignment();

        if ($bed->_curr_assignment != NULL) {
            PHPWS_Core::initModClass('hms', 'exception/HallStructureException.php');
            throw new HallStructureException('A student is currently assigned to that bed and therefore it cannot deleted.');
        }

        try {
            $result = $bed->delete();
        } catch (DatabaseException $e) {
            throw $e;
        }

        return true;
    }

    /**
     * *******************
     * Static UI Methods *
     * *******************
     */
    public static function bed_pager_by_room($room_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('hms_bed', 'HMS_Bed');
        $pager->db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');

        $pager->addWhere('hms_room.id', $room_id);
        $pager->db->addOrder('hms_bed.bedroom_label');
        $pager->db->addOrder('hms_bed.bed_letter');

        $page_tags['BEDROOM_LABEL'] = 'Bedroom';
        $page_tags['BED_LETTER_LABEL'] = 'Bed';
        $page_tags['ASSIGNED_TO_LABEL'] = 'Assigned to';
        $page_tags['RA_LABEL'] = 'RA Roommate bed';

        if (Current_User::allow('hms', 'bed_structure') && UserStatus::isAdmin()) {
            $addBedCmd = CommandFactory::getCommand('ShowAddBed');
            $addBedCmd->setRoomId($room_id);
            $page_tags['ADD_BED_LINK'] = $addBedCmd->getLink('Add bed');
        }

        $pager->setModule('hms');
        $pager->setTemplate('admin/bed_pager_by_room.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No beds found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerByRoomTags');
        $pager->addPageTags($page_tags);

        return $pager->get();
    }
}

?>