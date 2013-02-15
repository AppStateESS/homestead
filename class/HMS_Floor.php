<?php

/**
 * HMS Floor class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Floor extends HMS_Item
{
    public $term;
    public $floor_number;
    public $residence_hall_id;
    public $is_online;
    public $gender_type;
    public $f_movein_time_id;
    public $t_movein_time_id;
    public $rt_movein_time_id;
    public $rlc_id;
    public $floor_plan_image_id;

    /**
     * List of rooms associated with this floor
     * @vary array
     */
    public $_rooms     = null;

    /**
     * Holds the parent residence hall object of this floor
     */
    public $_hall      = null;

    /**
     * Constructor
     */
    public function HMS_Floor($id = 0)
    {
        $this->construct($id, 'hms_floor');
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_floor');
    }

    /********************
     * Instance Methods *
     *******************/

    /**
     * Saves a new or updated floor hall object
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_floor');

        $result = $db->saveObject($this);

        if(!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
    }

    /**
     * Copies this floor object to a new term, then calls copy on all
     * 'this' floor's rooms
     *
     * Setting $assignments to 'true' causes the copy public function to copy
     * the assignments as well as the hall structure.
     *
     * @return bool False if unsuccessful.
     */
    public function copy($to_term, $hall_id, $assignments = false, $roles = false)
    {
        if(!$this->id) {
            return false;
        }

        //echo "in hms_floor, copying this floor id: $this->id <br>";

        // Create a clone of the current floor object
        // Set id to 0, set term, and save
        $new_floor = clone($this);
        $new_floor->reset();
        $new_floor->term = $to_term;
        $new_floor->residence_hall_id = $hall_id;
        $new_floor->f_movein_time_id = null;
        $new_floor->t_movein_time_id = null;
        $new_floor->rt_movein_time_id = null;

        try{
            $new_floor->save();
        }catch(Exception $e) {
            throw $e;
        }

        // Copy any roles related to this floor.
        if($roles) {
            PHPWS_Core::initModClass("hms", "HMS_Permission.php");
            PHPWS_Core::initModClass("hms", "HMS_Role.php");
            // Get memberships by object instance.
            $membs = HMS_Permission::getUserRolesForInstance(null, $this);
            // Add each user to new floor
            foreach($membs as $m) {
                // Lookup the username
                $user = new PHPWS_User($m['user_id']);

                // Load role and add user to new instance
                $role = new HMS_Role();
                $role->id = $m['role'];
                $role->load();
                $role->addUser($user->getUsername(), get_class($new_floor), $new_floor->id);
            }
        }

        // Load all the rooms for this floor
        if(empty($this->_rooms)) {
            try{
                $this->loadRooms();
            }catch(Exception $e) {
                throw $e;
            }
        }

        /**
         * Rooms exist. Start making copies.
         * Further copying is needed at the room level.
         */

        if(!empty($this->_rooms)) {
            foreach ($this->_rooms as $room) {
                try{
                    $room->copy($to_term, $new_floor->id, null, $assignments);
                }catch(Exception $e) {
                    throw $e;
                }
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLink($prependText = null)
    {
        $floorCmd = CommandFactory::getCommand('EditFloorView');
        $floorCmd->setFloorId($this->id);
        if(!is_null($prependText)) {
            $text = $prependText . ' ' . $this->floor_number;
        } else {
            $text = $this->floor_number;
        }
        return $floorCmd->getLink($text);
    }

    /**
     * Loads the parent hall object of this floor
     */
    public function loadHall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $result = new HMS_Residence_Hall($this->residence_hall_id);
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        $this->_hall = & $result;
        return true;
    }

    /**
     * Pulls all the rooms associated with this floor and stores
     * them in the _room variable.
     */
    public function loadRooms()
    {

        $db = new PHPWS_DB('hms_room');
        $db->addWhere('floor_id', $this->id);
        $db->addOrder('room_number', 'ASC');

        $db->loadClass('hms', 'HMS_Room.php');
        $result = $db->getObjects('HMS_Room');
        //test($result);
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        } else {
            $this->_rooms = & $result;
            return true;
        }
    }

    /**
     * Creates the rooms and beds for a new floor
     */
    public function create_child_objects($rooms_per_floor, $beds_per_room)
    {
        for ($i = 0; $i < $rooms_per_floor; $i++) {
            $room = new HMS_Room;

            $room->floor_id     = $this->id;
            $room->term         = $this->term;
            $room->gender_type  = $this->gender_type;

            if($room->save()) {
                $room->create_child_objects($beds_per_room);
            } else {
                // Decide on bad Result.
            }
        }
    }

    /**
     * Returns true or false.
     *
     * This public function uses the following logic:
     *
     * When ignore_upper = true (a hall is trying to see if this floor can be changed to a target gender):
     *      If the target gender is COED: always return true, since it doesn't matter what the rooms are (or what the hall is)
     *      If the target gender is MALE: return false if any room is female and non-empty
     *      If the target gender is FEMALE: return false if any room is male and non-empty
     *      If all thsoe checks pass, then return true
     *
     *      When ignore_upper = false (we're trying to change *this* floor to a target gender):
     *      If the target gender is COED: return true only if the hall is COED (but it doesn't matter what the rooms are)
     *      If the target gender is MALE: return false if the hall is female, or if there are any female rooms on the floor
     *      If the target gender is FEMALE: return false if the hall is male, or if there are any male rooms on the floor
     *
     * @param int   target_gender
     * @param bool  ignore_upper
     * @return bool
     */
    public function can_change_gender($target_gender, $ignore_upper = false)
    {
        // Ignore upper is true, we're trying to change a hall's gender
        if($ignore_upper) {
            // If ignore upper is true and the target gender is coed, then
            // we can always return true.
            if($target_gender == COED) {
                return true;
            }

            // Can only change to male/female if there are no rooms of the opposite sex on this hall
            // TODO: This should check for rooms that are of the opposite sex AND not empty
            if($target_gender == MALE) {
                $check_for_gender = FEMALE;
            } else {
                $check_for_gender = MALE;
            }

            // If a check for rooms of the opposite gender returns true, then return false
            if($this->check_for_rooms_of_gender($check_for_gender)) {
                return false;
            }

        } else {
            // Ignore upper is false, load the hall and compare

            if(!$this->loadHall()) {
                // an error occured loading the hall
                return false;
            }

            // The target gender must match the hall's gender, unless the hall is COED
            if($this->_hall->gender_type != COED && $this->_hall->gender_type != $target_gender) {
                return false;
            }

            // Additionally, we need to check for rooms of the oppsite sex, unless the target gender is COED
            if($target_gender != COED) {
                // If a check for rooms of the opposite gender returns true, then return false
                test($target_gender);
                if($this->checkForOtherRoomGenders($target_gender)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function check_for_rooms_of_gender($gender_type)
    {
        $db = new PHPWS_DB('hms_room');

        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addWhere('hms_room.gender_type', $gender_type);
        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if($result == 0) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Returns true if there are any rooms on this floor set to a gender
     * *other than* the specificed gender. 
     * 
     * @param Integer $gender
     * @return boolean
     * @throws DatabaseException
     */
    public function checkForOtherRoomGenders($gender)
    {
        $db = new PHPWS_DB('hms_room');
        
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addWhere('hms_room.gender_type', $gender, '!=');
        $db->addWhere('hms_floor.id', $this->id);
        
        $result = $db->select('count');
        
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        
        if($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUsernames()
    {
        $db = new PHPWS_DB('hms_assignment');

        $db->addColumn('asu_username');

        $db->addJoin('LEFT OUTER', 'hms_assignment','hms_bed',            'bed_id',             'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',       'hms_room',           'room_id',            'id');
        $db->addJoin('LEFT OUTER', 'hms_room',      'hms_floor',          'floor_id',           'id');

        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('col');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public function getFloorNumber()
    {
        return $this->floor_number;
    }

    /*
     * Returns the number of rooms on the current floor
     */
    public function get_number_of_rooms()
    {
        $db = new PHPWS_DB('hms_room');

        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the number of beds on the current floor
     */
    public function get_number_of_beds()
    {
        $db = new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the number of assignees on the current floor
     */
    public function get_number_of_assignees()
    {
        $db = new PHPWS_DB('hms_assignment');

        $db->addJoin('LEFT OUTER', 'hms_assignment','hms_bed',            'bed_id',             'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',       'hms_room',           'room_id',            'id');
        $db->addJoin('LEFT OUTER', 'hms_room',      'hms_floor',          'floor_id',           'id');

        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');

        if($result == 0) {
            return $result;
        }

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the parent hall object of this floor
     */
    public function get_parent()
    {
        $this->loadHall();
        return $this->_hall;
    }

    /*
     * Returns an array of the rooms on the current floor
     */
    public function get_rooms()
    {
        if(!$this->loadRooms()) {
            return false;
        }

        return $this->_rooms;
    }

    /**
     * Returns an associative array where the keys are room ID's
     * and the values are the room numbers.
     */
    public function get_rooms_array()
    {
        if(!$this->loadRooms()) {
            return false;
        }

        $rooms = array();

        foreach($this->_rooms as $room) {
            $rooms[$room->id] = $room->room_number;
        }

        return $rooms;

    }

    /**
     * Returns an array of the bed on the current floor
     *
     * @return Array An array of HMS_Bed objects that exist on the current floor.
     */
    public function get_beds()
    {
        $beds = array();

        if(!$this->loadRooms()) {
            return false;
        }

        foreach($this->_rooms as $room) {
            $room_beds = $room->get_beds();
            $beds = array_merge($beds, $room_beds);
        }
        return $beds;
    }

    /**
     * Returns an array of student objects which are currently assigned to this floor
     */
    public function get_assignees()
    {
        if(!$this->loadRooms()) {
            return false;
        }

        $assignees = array();

        foreach($this->_rooms as $room) {
            $room_assignees = $room->get_assignees();
            $assignees = array_merge($assignees, $room_assignees);
        }

        return $assignees;
    }

    /**
     * Returns true if this floor has vancancies, false otherwise
     *
     * @return bool True if the floor has vacancies, false otherwise.
     */
    public function has_vacancy()
    {
        if($this->get_number_of_assignees() < $this->get_number_of_beds()) {
            return true;
        }

        return false;
    }

    /**
     * Returns an array of room objects on this floor that have vacancies
     *
     * @return Array<HMS_Room> An array of HMS_Room objects which are vacant on this floor.
     */
    public function getRoomsWithVacancies()
    {
        if(!$this->loadRooms()) {
            return false;
        }

        $vacant_rooms = array();

        foreach($this->_rooms as $room) {
            if($room->has_vacancy()) {
                $vacant_rooms[] = $room;
            }
        }

        return $vacant_rooms;
    }

    public function where_am_i($link = false)
    {
        $building = $this->get_parent();

        $text = $building->hall_name . ', floor ' . $this->floor_number;

        if($link) {
            $editFloorCmd = CommandFactory::getCommand('EditFloorView');
            $editFloorCmd->setFloorId($this->id);

            return $editFloorCmd->getLink($text);
        }else{
            return $text;
        }
    }

    public function count_avail_lottery_rooms($gender)
    {
        $now = mktime();

        // Calculate the number of non-full male/female rooms in this hall
        $query =   "SELECT DISTINCT COUNT(hms_room.id) FROM hms_room
                    JOIN hms_bed ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                    AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_floor.id = {$this->id}
        			AND hms_floor.rlc_id IS null
        			AND hms_floor.is_online = 1
                    AND hms_room.gender_type IN ($gender, 3)
                    AND hms_room.reserved = 0
                    AND hms_room.offline = 0
                    AND hms_room.private = 0
                    AND hms_room.ra = 0
                    AND hms_room.overflow = 0
                    AND hms_room.parlor = 0
                    AND hms_bed.international_reserved = 0";

        $avail_rooms = PHPWS_DB::getOne($query);
        if(PHPWS_Error::logIfError($avail_rooms)) {
            throw new DatabaseException($result->toString());
        }

        return $avail_rooms;
    }

    public function get_pager_by_hall_tags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl['FLOOR_NUMBER']   = $this->getLink();
        $tpl['GENDER_TYPE'] = HMS_Util::formatGender($this->gender_type);
        $tpl['IS_ONLINE']   = $this->is_online ? 'Yes' : 'No';

        return $tpl;
    }

    /******************
     * Static Methods *
     *****************/

    public static function get_pager_by_hall($hall_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('hms_floor', 'HMS_Floor');

        $pager->addWhere('hms_floor.residence_hall_id', $hall_id);
        $pager->db->addOrder('hms_floor.floor_number');

        $page_tags['TABLE_TITLE']       = 'Floors in this hall';
        $page_tags['FLOOR_NUM_LABEL']   = 'Floor #';
        $page_tags['GENDER_LABEL']      = 'Gender';
        $page_tags['ONLINE_LABEL']      = 'Online';

        $pager->setModule('hms');
        $pager->setTemplate('admin/floor_pager_by_hall.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No floors found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('get_pager_by_hall_tags');
        $pager->addPageTags($page_tags);

        return $pager->get();
    }
}
?>
