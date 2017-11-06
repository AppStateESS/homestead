<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;
use \Homestead\Exception\PermissionException;
use \Homestead\Exception\HallStructureException;
use \PHPWS_Error;
use \PHPWS_DB;

/**
 * HMS Room class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class Room extends HMS_Item
{

    public $term;
    public $floor_id               = 0;
    public $room_number            = 0;

    // Gender
    public $gender_type            = 0;
    public $default_gender         = 0;

    // Reservations
    public $reserved               = false;
    public $reserved_reason        = "";
    public $reserved_notes         = "";
    public $offline                = false;
    public $ra                     = false;
    public $private                = false;
    public $overflow               = false;
    public $parlor                 = false;
    public $reserved_rlc_id;

    // Medical flags
    public $ada                    = false;
    public $hearing_impaired       = false;
    public $bath_en_suite          = false;

    // Persistent ID for identifying this room across semesters
    public $persistent_id;

    /****************************************************
     * Following fields are not present in the database *
    ****************************************************/
    public $banner_building_code;

    /**
     * Listing of beds associated with this room
     * @var array
     */
    public $_beds                  = null;

    /**
     * Parent Floor object of this room
     * @public object
     */
    public $_floor                 = null;

    /* Hack for the javascript DO NOT TOUCH */
    public $message = '';
    public $value   = false;

    /**
     * Constructor
     */
    public function __construct($id = 0)
    {
        parent::__construct($id, 'hms_room');
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_room');
    }

    /********************
     * Instance Methods *
    *******************/

    /*
     * Saves a new or updated floor hall object
    * New room ids are inserted into the id variable.
    * Save errors are logged.
    *
    * @return bool True is successful, false otherwise.
    */
    public function save()
    {
        $this->stamp();
        $db = new PHPWS_DB('hms_room');
        $result = $db->saveObject($this);

        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        return true;
    }

    public function delete()
    {
        if (is_null($this->id) || !isset($this->id)) {
            throw new \InvalidArgumentException('Invalid room id.');
        }

        $db = new PHPWS_DB('hms_room');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    /*
     * Copies this room object to a new term, then calls copy on all
    * 'this' room's beds.
    *
    * Setting $assignments to true causes the copy public function to copy
    * the c<urrent assignments as well as the hall structure.
    *
    * @return bool False if unsuccessful.
    */
    public function copy($to_term, $floor_id, $suite_id=NULL, $assignments = false)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_room, cloning room with id: $this->id <br>";

        // Create clone of current room object
        // Set id to 0, set term, and save
        $new_room = clone($this);
        $new_room->reset();
        $new_room->term     = $to_term;
        $new_room->floor_id = $floor_id;

        //Set the default gender to the floor's gender if the floor isn't
        //coed and the genders don't match.
        $new_room->loadFloor();
        if ($new_room->_floor->gender_type != COED
                && $new_room->default_gender != $new_room->_floor->gender_type)
        {
            $new_room->default_gender = $new_room->_floor->gender_type;
        }

        //If we're not coyping assignments, then set the gender of the room to the room's default gender
        // Resetting the gender when copying the assignemnt can result in students assigned to rooms of a different gender
        // Because this manipulates the database directly, the genders don't get checked
        if (!$assignments) {
            $new_room->gender_type = $new_room->default_gender;
        }
        else if ($assignments) {
            $new_room->gender_type = $this->gender_type;
        }

        try{
            $new_room->save();
        }catch(\Exception $e) {
            throw $e;
        }

        // Save successful, create new beds

        // Load all beds for this room
        if (empty($this->_beds)) {
            try{
                $this->loadBeds();
            }catch(\Exception $e) {
                throw $e;
            }
        }

        /**
         * Beds exist. Start making copies.
         * Further copying is needed at the bed level.
         * The bed class will work much like this class. If assignments is true then
         * beds will load beds and assignments, foreach the bed list, and
         * $bed->copy($to_term, $assignments) once again with username copied, etc.
         *
         **/

        if (!empty($this->_beds)) {
            foreach ($this->_beds as $bed) {
                try{
                    $bed->copy($to_term, $new_room->id, $assignments);
                }catch(\Exception $e) {
                    throw $e;
                }
            }
        }
    }

    public function getLink($prependText = NULL)
    {
        $roomCmd = CommandFactory::getCommand('EditRoomView');
        $roomCmd->setRoomId($this->id);
        if (!is_null($prependText)) {
            $text = $prependText . ' ' . $this->room_number;
        } else {
            $text = $this->room_number;
        }
        return $roomCmd->getLink($text);
    }

    /**
     * Loads the parent floor object of this room
     */
    public function loadFloor()
    {
        $result = new Floor($this->floor_id);
        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        $this->_floor = & $result;
        return true;
    }

    /**
     * Pulls all beds associated with this room and stores them in
     * the _beds variable.
     *
     */
    public function loadBeds()
    {
        $db = PdoFactory::getPdoInstance();
        $sql = "SELECT *
            FROM hms_bed
            WHERE room_id = :id
            ORDER BY bedroom_label, bed_letter ASC";
        $sth = $db->prepare($sql);
        $sth->execute(array('id' => $this->id));
        $this->_beds = $sth->fetchAll(\PDO::FETCH_CLASS, '\Homestead\BedRestored');

        return true;
    }

    /*
     * Creates beds for a new room
    * Initial values for beds should be set in the declaration.
    * Assuming gender_type is carried over.
    * added and updated variables need to be set in the bed save public function.
    */
    public function create_child_objects($bedrooms_per_room)
    {
        for ($i = 0; $i < $bedroooms_per_room; $i++) {
            $bed = new Bed;

            $bed->room_id     = $this->id;
            $bed->term        = $this->term;
            $bed->gender_type = $this->gender_type;
        }
    }

    /*
     * Returns true or false.
    *
    * This public function uses the following logic:
    *
    * When ignore_upper = true (a floor is trying to see if this room could be changed to a target gender):
    *      If the target gender is COED: then we can always return true (even though a room can never be COED).
    *      If the target gender is MALE: then return false if the room is female AND not empty
    *      If the target gender is FEMALE: then return false if the room is male AND not empty
    *      If all those checks pass, then return true
    *
    * When ignore_upper = false (we're trying to change *this* room to a target gender):
    *      If the target gender is COED: always return false (rooms can't be COED)
    *      If the target gender is MALE: return false if the floor is female
    *      If the target gender is FEMALE: return false if the floor is male
    *
    * @param int  target_gender
    * @param bool ignore_upper In the case that we're attempting to change
    *                          the gender of just 'this' room, set $ignore_upper
    *                          to true to avoid checking the parent hall's gender.
    * @return bool
    */
    public function can_change_gender($target_gender, $ignore_upper = false)
    {
        // Ignore upper is true, we're trying to change a hall/floor
        if ($ignore_upper) {
            // If ignore upper is true and the target gender coed, then we
            // can always return true.
            if ($target_gender == COED) {
                return true;
            }

            // If the target gender is not the same, and someone is assigned
            // here, then the gender can't be changed (i.e. return false)
            if (($target_gender != $this->gender_type) && ($this->get_number_of_assignees() != 0)) {
                return false;
            }

            return true;
        } else {
            // Ignore upper is false, load the floor and compare

            // If the target gender is not the same, and someone is assigned
            // here, then the gender can't be changed (i.e. return false)
            if (($target_gender != $this->gender_type) && ($this->get_number_of_assignees() != 0)) {
                return false;
            }

            if (!$this->loadFloor()) {
                // an error occurred loading the floor, check logs
                return false;
            }

            // If the floor is not coed and the gt is not the target, return false
            if ($this->_floor->gender_type != COED &&
                    $this->_floor->gender_type != $target_gender
            ) {
                return false;
            }

            return true;
        }
    }

    /*
     * Returns the number of beds within the current room
    */
    public function get_number_of_beds()
    {
        $db = PdoFactory::getPdoInstance();
        $sql = "SELECT hms_bed.id
            FROM hms_bed
            LEFT JOIN hms_room
            ON room_id = hms_room.id
            WHERE hms_room.id = :id";
        $sth = $db->prepare($sql);
        $sth->execute(array('id' => $this->id));
        $result = $sth->rowCount();

        return $result;
    }

    /*
     * Returns the number of students assigned to the current room
    */
    public function get_number_of_assignees()
    {
        $db = PdoFactory::getPdoInstance();
        $sql = "SELECT hms_assignment.id
            FROM hms_assignment
            LEFT JOIN hms_bed
            ON bed_id = hms_bed.id
            LEFT JOIN  hms_room
            ON room_id = hms_room.id
            WHERE hms_room.id = :id";
        $sth = $db->prepare($sql);
        $sth->execute(array('id' => $this->id));
        $result = $sth->rowCount();

        return $result;
    }

    /*
     * Returns the parent floor object of this room
    */
    public function get_parent()
    {
        $this->loadFloor();
        return $this->_floor;
    }

    /*
     * Returns an array of beds within the current room
    */
    public function get_beds()
    {
        if (!$this->loadBeds()) {
            return false;
        }

        return $this->_beds;
    }

    /**
     * Returns an associative array where the keys are bed ID's
     * and the values are the bed letter.
     */
    public function get_beds_array()
    {
        if (!$this->loadBeds()) {
            return false;
        }

        $beds = array();

        foreach($this->_beds as $bed) {
            $beds[$bed->id] = $bed->bed_letter;
        }

        return $beds;
    }

    /*
     * Returns an array of Student objects which are currently
    * assigned to 'this' room.
    */
    public function get_assignees()
    {
        if (!$this->loadBeds()) {
            return false;
        }

        $assignees = array();

        if($this->_beds != null){
            foreach ($this->_beds as $bed) {
                $assignee = $bed->get_assignee();
                if (!is_null($assignee)) {
                    $assignees[] = $assignee;
                }
            }
        }

        return $assignees;
    }

    /**
     * Returns true if the hall has vacant beds, false otherwise
     */
    public function has_vacancy()
    {
        $num_assigned = $this->get_number_of_assignees();

        // If this is a private room, then this room is full if one person is assigned
        if ($this->isPrivate() && $num_assigned >= 1) {
            return false;
        }

        if ($num_assigned < $this->get_number_of_beds()) {
            $this->loadBeds();
            $vacant = false;
            foreach($this->_beds as $bed) {
                $bed->loadAssignment();
                if (is_null($bed->_curr_assignment)) {
                    $vacant = true;
                }
            }
            return $vacant;
        }

        return false;
    }

    /**
     * Returns an array of bed objects in this room that have vacancies
     */
    public function getBedsWithVacancies()
    {
        if (!$this->loadBeds()) {
            return false;
        }

        $vacant_beds = array();

        // Search for vacant beds in this room's set of beds, only if this room
        // has a vacancy according to 'has_vacancy()'. This accounts for private rooms.
        if ($this->has_vacancy()) {

            foreach($this->_beds as $bed) {
                if ($bed->has_vacancy()) {
                    $vacant_beds[] = $bed;
                }
            }
        }

        return $vacant_beds;
    }

    public function where_am_i($link = false)
    {
        $floor = $this->get_parent();
        $building = $floor->get_parent();

        $text = $building->hall_name . ' Room ' . $this->room_number;

        if ($link) {
            return \PHPWS_Text::secureLink($text, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$this->id));
        } else {
            return $text;
        }
    }

    public function count_avail_lottery_beds()
    {
        $now = time();

        $db = PdoFactory::getPdoInstance();
        // Count the number of beds which are free in this room
        $query =   "SELECT DISTINCT hms_bed.id FROM hms_bed
        JOIN hms_room ON hms_bed.room_id = hms_room.id
        WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = :term AND expires_on > :now)
        AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = :term))
        AND hms_room.id = :id
        AND hms_room.reserved = 0
        AND hms_room.offline = 0
        AND hms_room.private = 0
        AND hms_room.overflow = 0
        AND hms_room.parlor = 0
        AND hms_bed.international_reserved = 0
        AND hms_bed.ra = 0
        AND hms_bed.ra_roommate = 0";

        $sth = $db->prepare($query);
        $sth->execute(array('id' => $this->id, 'term' => $this->term, 'now' => $now));
        $avail_rooms = $sth->rowCount();

        return $avail_rooms;
    }

    /**
     * DBPager row method for the floor view
     *
     * @return Array
     */
    public function get_row_tags()
    {
        $tpl = array();

        $tpl['ID']             = $this->id;
        $tpl['ROOM_NUMBER']    = $this->getLink();
        $tpl['GENDER_TYPE']    = HMS_Util::formatGender($this->gender_type);
        $tpl['DEFAULT_GENDER'] = HMS_Util::formatGender($this->default_gender);

        $rlcList = RlcFactory::getRlcList($this->term);
        $rlcReservation = $this->getReservedRlcId();
        if($rlcReservation != null) {
            $tpl['RLC_RESERVED'] = $rlcList[$rlcReservation];
        }

        $tpl['RA']             = $this->isRa()        ? 'Yes' : 'No';
        $tpl['PRIVATE']        = $this->isPrivate()   ? 'Yes' : 'No';
        $tpl['OVERFLOW']       = $this->isOverflow()  ? 'Yes' : 'No';
        $tpl['RESERVED']       = $this->isReserved()  ? 'Yes' : 'No';
        $tpl['OFFLINE']        = $this->isOffline()   ? 'Yes' : 'No';
        $tpl['ADA']            = $this->isADA()       ? 'Yes' : 'No';

        if (\Current_User::allow('hms','room_structure') && $this->get_number_of_assignees() == 0) {
            $deleteRoomCmd = CommandFactory::getCommand('DeleteRoom');
            $deleteRoomCmd->setRoomId($this->id);
            $deleteRoomCmd->setFloorId($this->floor_id);

            $confirm             = array();
            $confirm['QUESTION'] = 'Are you sure want to delete room ' .  $this->room_number . '?';
            $confirm['ADDRESS']  = $deleteRoomCmd->getURI();
            $confirm['LINK']     = 'Delete';
        }

        return $tpl;
    }

    /**
     * DBPager row method for the floor edit pager.
     *
     * @return Array
     */
    public function get_row_edit() {
        javascript('jquery');
        $tpl = array();
        $tpl['ID']           = $this->id;
        $tpl['ROOM_NUMBER']  = \PHPWS_Text::secureLink($this->room_number, 'hms', array('action'=>'EditRoomView', 'room'=>$this->id));

        if (\Current_User::allow('hms','room_structure') && $this->get_number_of_assignees() == 0) {
            $deleteRoomCmd = CommandFactory::getCommand('DeleteRoom');
            $deleteRoomCmd->setRoomId($this->id);
            $deleteRoomCmd->setFloorId($this->floor_id);

            $confirm             = array();
            $confirm['QUESTION'] = 'Are you sure want to delete room ' .  $this->room_number . '?';
            $confirm['ADDRESS']  = $deleteRoomCmd->getURI();
            $confirm['LINK']     = 'Delete';
            $tpl['DELETE']       = \Layout::getJavascript('confirm', $confirm);
        }

        $form = new \PHPWS_Form($this->id);
        $form->addSelect('gender_type', array(FEMALE => FEMALE_DESC,
                MALE   => MALE_DESC,
                COED   => COED_DESC,
                AUTO   => AUTO_DESC
        ));

        $form->setMatch('gender_type', $this->gender_type);
        $form->setExtra('gender_type', 'onChange="submit_form(this, true)"');

        $form->addSelect('default_gender', array(FEMALE => FEMALE_DESC,
                MALE   => MALE_DESC,
                AUTO   => AUTO_DESC
        ));
        $form->setMatch('default_gender', $this->default_gender);
        $form->setExtra('default_gender', 'onChange="submit_form(this, true)"');

        $form->addSelect('rlc_reserved', array('-1'=>'Select RLC') + RlcFactory::getRlcList($this->term));
        $form->setMatch('rlc_reserved', $this->getReservedRlcId());
        $form->setExtra('rlc_reserved', 'onChange="submit_form(this, true)"');

        $form->addCheck('offline', 'yes');
        $form->setMatch('offline', $this->offline == 1 ? 'yes' : 0);
        $form->setExtra('offline', 'onChange="submit_form(this, false)"');

        $form->addCheck('reserved', 'yes');
        $form->setMatch('reserved', $this->reserved == 1 ? 'yes' : 0);
        $form->setExtra('reserved', 'onChange="submit_form(this, false)"');

        $form->addCheck('ra', 'yes');
        $form->setMatch('ra', $this->ra == 1 ? 'yes' : 0);
        $form->setExtra('ra', 'onChange="submit_form(this, false)"');

        $form->addCheck('private', 'yes');
        $form->setMatch('private', $this->private == 1 ? 'yes' : 0);
        $form->setExtra('private', 'onChange="submit_form(this, false)"');

        $form->addCheck('overflow', 'yes');
        $form->setMatch('overflow', $this->overflow == 1 ? 'yes' : 0);
        $form->setExtra('overflow', 'onChange="submit_form(this, false)"');

        $form->addCheck('ada', 'yes');
        $form->setMatch('ada', $this->isAda() ? 'yes' : 0);
        $form->setExtra('ada', 'onChange="submit_form(this, false)"');

        $form->addHidden('action', 'UpdateRoomField');
        $form->addHidden('room', $this->id);

        $form->mergeTemplate($tpl);

        //test($form->getTemplate(),1);

        return $form->getTemplate();
    }

    /******************************
     * Accessor / Mutator Methods *
    ******************************/

    public function getId()
    {
        return $this->id;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getPersistentId()
    {
        return $this->persistent_id;
    }

    public function getRoomNumber()
    {
        return $this->room_number;
    }

    public function isOffline()
    {
        return $this->offline == 1 ? true : false;
    }

    public function setOffline($value)
    {
        $this->offline = $value;
    }

    public function isReserved()
    {
        return $this->reserved == 1 ? true : false;
    }

    public function setReserved($value) {
        $this->reserved = $value;
    }

    public function getReservedReason()
    {
        return $this->reserved_reason;
    }

    public function setReservedReason($reason)
    {
        $this->reserved_reason = $reason;
    }

    public function getReservedNotes()
    {
        return $this->reserved_notes;
    }

    public function setReservedNotes($notes)
    {
        $this->reserved_notes = $notes;
    }

    public function isRa()
    {
        return $this->ra == 1 ? true : false;
    }

    public function setRa($value) {
        $this->ra = $value;
    }

    public function isPrivate()
    {
        return $this->private == 1 ? true : false;
    }

    public function setPrivate($value) {
        $this->private = $value;
    }

    public function isOverflow()
    {
        return $this->overflow == 1 ? true : false;
    }

    public function setOverflow($value) {
        $this->overflow = $value;
    }

    public function isADA()
    {
        return $this->ada == 1 ? true : false;
    }

    public function setADA($value) {
        $this->ada = $value;
    }

    public function isHearingImpaired()
    {
        return $this->hearing_impaired == 1 ? true : false;
    }

    public function setHearingImpaired() {
        $this->hearing_impaired = $value;
    }

    public function bathEnSuite()
    {
        return $this->bath_en_suite == 1 ? true : false;
    }

    public function setBathEnSuite($value) {
        $this->bath_en_suite = $value;
    }

    public function isParlor()
    {
        return $this->parlor == 1 ? true : false;
    }

    public function setParlor($value) {
        $this->parlor = $value;
    }

    public function getReservedRlcId()
    {
    	return $this->reserved_rlc_id;
    }

    public function setReservedRlcId($id)
    {
    	$this->reserved_rlc_id = $id;
    }

    public function getGender() {
        return $this->gender_type;
    }

    public function setGender($gender) {
        $this->gender_type = $gender;
    }

    public function getDefaultGender() {
        return $this->default_gender;
    }

    public function setDefaultGender($gender) {
        $this->default_gender = $gender;
    }

    /******************
     * Static Methods *
    *****************/

    public static function room_pager_by_floor($floor_id, $editable=false)
    {
        javascript('jquery');

        $pager = new \DBPager('hms_room', '\Homestead\Room');
        $pager->addWhere('hms_room.floor_id', $floor_id);
        $pager->db->addOrder('hms_room.room_number');

        $page_tags = array();

        if (\Current_User::allow('hms', 'room_structure')) {
            $addRoomCmd = CommandFactory::getCommand('ShowAddRoom');
            $addRoomCmd->setFloorId($floor_id);
            $page_tags['ADD_ROOM_URI'] = $addRoomCmd->getURI();
        }

        $pager->limitList = array(200);
        $pager->setDefaultLimit(200);

        $pager->setModule('hms');
        $pager->setTemplate('admin/room_pager_by_floor.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage('No rooms found.');

        if ($editable) {
            $pager->addRowTags('get_row_edit');
            $page_tags['FORM'] = 'form=true';
        } else {
            $page_tags['FORM'] = 'form=false';
            $pager->addRowTags('get_row_tags');
        }
        $pager->addPageTags($page_tags);

        return $pager->get();
    }

    /*
     * Deletes a room and any beds in it.  Returns true
    * if we're successful, false if not (or if there
            * is an assignment)
    */
    public static function deleteRoom($roomId)
    {

        if (!\Current_User::allow('hms', 'room_structure')) {
            throw new PermissionException('You do not have permission to delete a room.');
        }

        // check that we're not about to do something stupid
        if (!isset($roomId)) {
            throw new \InvalidArgumentException('Invalid room id.');
        }

        $room = new Room($roomId);

        // make sure there isn't an assignment
        if ($room->get_number_of_assignees() != 0) {
            throw new HallStructureException('One or more students are currently assigned to that room and therefore it cannot deleted.');
        }

        // delete any beds
        try{
            if ($room->loadBeds()) {
                if (!empty($room->_beds)) {
                    foreach($room->_beds as $bed) {
                        Bed::deleteBed($bed->id);
                    }
                }
            }

            $room->delete();
        }catch(\Exception $e) {
            throw $e;
        }

        return true;
    }

    /**
     * Returns the ID of an empty room (which can be auto-assigned)
     * Returns false if there are no more free rooms
     */
    // TODO: finish this, see Trac #156
    public static function get_free_room($term, $gender, $randomize = false)
    {
        // Only get free rooms
        $db = PdoFactory::getPdoInstance();
        $sql = "SELECT hms_room.id
            FROM hms_room
            LEFT JOIN hms_bed
            ON room_id = hms_room.id
            LEFT JOIN hms_assignment
            ON bed_id = hms_assignment.id";
        $sth = $db->prepare($sql);
        $sth->execute(array());
    }

    // Was not being called or working before update to PDO, so check if it's returning correctly if you do call it
    // This calls check_two_bed_and_empty_by_id so that will also have to be checked since this is the only call to it
    public static function getAllFreeRooms($term)
    {
        // Join other tables so we can do the other 'assignable' checks
        // Only get rooms with free beds
        // Order by gender preference (0=>female, 1=>male, 2=>coed), rooms in a single gender hall will be first
        // Make sure everything is online, nothing is reserved
        // Don't get RA beds, lobbies, private rooms, or rooms on floors reserved for an RLC
        $db = PdoFactory::getPdoInstance();
        $sql = "SELECT DISTINCT hms_room.id
            FROM hms_room
            LEFT JOIN hms_bed
            ON room_id = hms_room.id
            LEFT JOIN hms_floor
            ON floor_id = hms_room.id
            LEFT JOIN hms_residence_hall
            ON residence_hall_id = hms_residence_hall.id
            LEFT JOIN hms_assignment
            ON bed_id = hms_bed.id
            WHERE hms_room.term = :term AND hms_assignment.asu_username = NULL AND hms_room.offline = 0
            AND hms_floor.is_online = 1 AND hms_residence_hall.is_online = 1 AND hms_room.reserved = 0
            AND hms_room.ra = 0 AND hms_room.overflow = 0 AND  hms_room.private = 0 AND hms_floor.rlc_id = NULL
            ORDER BY hms_residence_hall.gender_type ASC";
        $sth = $db->prepare($sql);
        $sth->execute(array('term' => $term));
        $result = $sth->fetchAll(\PDO::FETCH_COLUMN);

        // Make sure each room is empty and has only two beds
        $ret = array_values(array_filter($result,
                array('Room', 'check_two_bed_and_empty_by_id')));

        if ($randomize) {
            shuffle($ret);
        }

        return $ret;
    }

    public static function check_two_bed_and_empty_by_id($room)
    {
        $db = PdoFactory::getPdoInstance();
        $sql = "SELECT hms_bed.id, hms_assignment.id
            FROM hms_bed
            LEFT JOIN hms_assignment
            ON bed_id = hms_bed.id
            WHERE room_id = :id AND term = :term";
        $sth = $db->prepare($sql);
        $sth->execute(array('id' => $room, 'term' => Term::getSelectedTerm()));
        $result = $sth->fetchAll(\PDO::FETCH_COLUMN);

        // If not two-bedroom, toss it out
        if (count($result) != 2) {
            return false;
        }

        foreach($result as $r) {
            // If anyone is assigned, toss it out
            if ($r != NULL) {
                return false;
            }
        }

        // Looks like we're good.
        return true;
    }

    public static function listReserveReasons()
    {
        $athletics[] = array('NAME' => 'Football', 'VALUE' => 'football');
        $athletics[] = array('NAME' => 'Basketball', 'VALUE' => 'basketball');
        $athletics[] = array('NAME' => 'Baseball', 'VALUE' => 'baseball');
        $athletics[] = array('NAME' => 'Soccer', 'VALUE' => 'soccer');
        $athletics[] = array('NAME' => 'Cross Country/Track', 'VALUE' => 'track');
        $athletics[] = array('NAME' => 'Field Hockey', 'VALUE' => 'fieldhockey');
        $athletics[] = array('NAME' => 'Wrestling', 'VALUE' => 'wrestling');
        $athletics[] = array('NAME' => 'Softball', 'VALUE' => 'softball');
        $athletics[] = array('NAME' => 'Volleyball', 'VALUE' => 'volleyball');
        $athletics[] = array('NAME' => 'Tennis', 'VALUE' => 'tennis');
        $athletics[] = array('NAME' => 'Golf', 'VALUE' => 'golf');

        $specialNeeds[] = array('NAME' => 'SDAP', 'VALUE' => 'sdap');
        $specialNeeds[] = array('NAME' => 'LGBT', 'VALUE' => 'lgbt');
        $specialNeeds[] = array('NAME' => 'ODS Support', 'VALUE' => 'ods');

        $scholarsOrgs[] = array('NAME' => 'Plemmons', 'VALUE' => 'plemmons');
        $scholarsOrgs[] = array('NAME' => 'Chancellors', 'VALUE' => 'chancellors');
        $scholarsOrgs[] = array('NAME' => 'Diversity', 'VALUE' => 'diversity');
        $scholarsOrgs[] = array('NAME' => 'RHA', 'VALUE' => 'rha');
        $scholarsOrgs[] = array('NAME' => 'NRHH', 'VALUE' => 'nrhh');

        $miscellaneous[] = array('NAME' => 'See Notes Section', 'VALUE' => 'notes');
        $miscellaneous[] = array('NAME' => 'Tour Room', 'VALUE' => 'tour');

        $reserveReasons = array('Athletics' => $athletics,
                                'SpecialNeeds' => $specialNeeds,
                                'ScholarsOrganizations' => $scholarsOrgs,
                                'Miscellaneous' => $miscellaneous);
        return $reserveReasons;
    }

    public function __tostring()
    {
        return ($this->banner_building_code ? $this->banner_building_code . ' ' : '') . $this->room_number;
    }
}
