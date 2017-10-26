<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;
use \PHPWS_Error;
use \PHPWS_DB;

/**
 * HMS Residence Hall class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 *         Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Residence_Hall extends HMS_Item {

    public $hall_name = NULL;
    public $term;
    public $banner_building_code = NULL;
    public $gender_type = 2;
    public $air_conditioned = 0;
    public $is_online = 0;
    public $meal_plan_required = 0;
    public $assignment_notifications = 1;

    // Photo IDs
    public $exterior_image_id;
    public $other_image_id;
    public $map_image_id;
    public $room_plan_image_id;

    // Package desk id
    public $package_desk;

    /**
     * Listing of floors associated with this room
     *
     * @var array
     */
    public $_floors = null;

    /**
     * Temporary values for rh creation
     */
    public $_number_of_floors = 0;
    public $_rooms_per_floor = 0;
    public $_beds_per_room = 0;
    public $_numbering_scheme = 0;

    /**
     * Constructor
     */
    public function __construct($id = 0)
    {
        parent::__construct($id, 'hms_residence_hall');
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_residence_hall');
    }

    /**
     * ******************
     * Instance Methods *
     * *****************
     */

    /*
     * Saves a new or updated residence hall object
    */
    public function save()
    {
        $this->stamp();
        $db = new PHPWS_DB('hms_residence_hall');
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        return true;
    }

    /*
     * Copies this residence hall object to a new term, then calls copy
    * on all 'this' room's floors.
    *
    * Setting $assignments to TRUE causes the copy public function to copy
    * the current assignments as well as the hall structure.
    *
    * @return bool False if unsuccessful.
    */
    public function copy($to_term, $assignments = FALSE, $roles = FALSE)
    {
        if (!$this->id) {
            return false;
        }

        // echo "In hms_residence_hall, copying this hall: $this->id <br>";

        // Create clone of current room object
        // Set id to 0, set term, and save
        $new_hall = clone ($this);
        $new_hall->reset();
        $new_hall->id = 0;
        $new_hall->term = $to_term;

        try {
            $new_hall->save();
        } catch (\Exception $e) {
            // rethrow it to the top level
            throw $e;
        }

        // Copy any roles related to this residence hall.
        if ($roles) {
            // Get memberships by object instance.
            $membs = HMS_Permission::getUserRolesForInstance($this);
            // test($membs,1);
            // Add each user to new hall
            foreach ($membs as $m) {
                // Lookup the username
                $user = new \PHPWS_User($m['user_id']);

                // Load role and add user to new instance
                $role = new HMS_Role();
                $role->id = $m['role'];
                $role->load();
                $role->addUser($user->getUsername(), get_class($new_hall), $new_hall->id);
            }
        }

        // Save successful, create new floors

        // Load all floors for this hall
        if (empty($this->_floors)) {
            try {
                $this->loadFloors();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        // Floors exist, start making copies
        if (!empty($this->_floors)) {
            foreach ($this->_floors as $floor) {
                try {
                    $floor->copy($to_term, $new_hall->id, $assignments, $roles);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Pulls all the floors associated with this hall and stores them in
     * the _floors variable.
     */
    public function loadFloors()
    {
        if (!$this->id) {
            $this->_floor = null;
            return null;
        }

        $db = new PHPWS_DB('hms_floor');
        $db->addWhere('residence_hall_id', $this->id);
        $db->addOrder('floor_number', 'ASC');

        $db->loadClass('hms', 'HMS_Floor.php');
        $result = $db->getObjects('\Homestead\HMS_Floor');
        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        $this->_floors = & $result;
        return true;
    }

    /*
     * Creates the floors, rooms, and beds for a new hall
    */
    public function create_child_objects($num_floors, $rooms_per_floor, $beds_per_room)
    {
        if (!$this->id) {
            return false;
        }

        for ($i = 0; $i < $num_floors; $i++) {
            $floor = new HMS_Floor();

            $floor->residence_hall_id = $this->id;
            $floor->term = $this->term;
            $floor->gender_type = $this->gender_type;

            if ($floor->save()) {
                $floor->create_child_objects($rooms_per_floor, $beds_per_room);
            } else {
                // Decide on bed result.
            }
        }
    }

    /*
     * Returns TRUE or FALSE. The gender of a building can only be
    * changed to the target gender if all floors can be changed
    * to the target gender.
    *
    * This public function checks to make sure all floors can be changed,
    * those floors in tern check all thier rooms, and so on.
    */
    // ODO: rewrite this becase the behavior changed
    public function can_change_gender($target_gender)
    {
        // You can always change to a COED gender.
        if ($target_gender == COED) {
            return true;
        }

        // We must be changing to either male or female if we make it here

        // If there are any COED floors, then return false
        if ($this->check_for_floors_of_gender(COED)) {
            return false;
        }

        // Can only change gender if there are no floors of the opposite sex
        if ($target_gender == MALE) {
            $check_for_gender = FEMALE;
        } else {
            $check_for_gender = MALE;
        }

        // If a check for rooms of the opposite gender returns true, then return false
        if ($this->check_for_floors_of_gender($check_for_gender)) {
            return false;
        }

        return true;
    }

    public function check_for_floors_of_gender($gender_type)
    {
        $db = new PHPWS_DB('hms_floor');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');
        $db->addWhere('hms_floor.gender_type', $gender_type);
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if ($result == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getHallName()
    {
        return $this->hall_name;
    }

    public function isOnline()
    {
        if($this->is_online){
            return true;
        }

        return false;
    }

    public function getLink()
    {
        $editHallCmd = CommandFactory::getCommand('EditResidenceHallView');
        $editHallCmd->setHallId($this->getId());
        return $editHallCmd->getLink($this->getHallName());
    }

    /*
     * Returns the number of floors in the current hall
    */
    public function get_number_of_floors()
    {
        $db = new PHPWS_DB('hms_floor');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the number of rooms in the current hall
    */
    public function get_number_of_rooms()
    {
        $db = new PHPWS_DB('hms_room');

        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if ($result == 0) {
            return 0;
        }

        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the number of beds in the current hall
    */
    public function get_number_of_beds()
    {
        $db = new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if ($result == 0) {
            return 0;
        }

        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result);
        }

        return $result;
    }

    /**
     * @deprecated
     * @see countNominalBeds()
     */
    public function get_number_of_online_nonoverflow_beds()
    {
        return $this->countNominalBeds();
    }

    public function countNominalBeds()
    {
        $db = new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);
        $db->addWhere('hms_room.offline', 0);
        $db->addWhere('hms_room.overflow', 0);
        $db->addWhere('hms_room.parlor', 0);

        $result = $db->select('count');

        if ($result == 0) {
            return 0;
        }

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the number of students currently assigned to the current hall
    */
    public function get_number_of_assignees()
    {
        $db = new PHPWS_DB('hms_assignment');

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if ($result == 0) {
            return 0;
        }

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if ($result == 0) {
            return $result;
        }

        return $result;
    }

    /*
     * Returns an array of floor objects which are within the current hall.
    */
    public function &get_floors()
    {
        if (!$this->loadFloors()) {
            return false;
        }

        return $this->_floors;
    }

    public function getFloors()
    {
        $this->loadFloors();
        return $this->_floors;
    }

    /*
     * Returns an array with the keys being floor ID's and the value being the floor number
    */
    public function get_floors_array()
    {
        if (!$this->loadFloors()) {
            return false;
        }

        $floors = array();

        foreach ($this->_floors as $floor) {
            $floors[$floor->id] = $floor->floor_number;
        }

        return $floors;
    }

    /*
     * Returns an array of room objects which are in the current hall
    */
    public function &get_rooms()
    {
        if (!$this->loadFloors()) {
            return false;
        }

        $rooms = array();

        foreach ($this->_floors as $floor) {
            $floor_rooms = $floor->get_rooms();
            if(!empty($floor_rooms))
            {
              $rooms = array_merge($rooms, $floor_rooms);
            }
        }
        return $rooms;
    }

    /*
     * Returns an array of the bed objects which are in the current hall
    */
    public function &get_beds()
    {
        if (!$this->loadFloors()) {
            return false;
        }

        $beds = array();

        foreach ($this->_floors as $floor) {
            $floor_beds = $floor->get_beds();
            $beds = array_merge($rooms, $floor_beds);
        }
        return $beds;
    }

    /**
     * Determines the number of beds per room in a hall.  If the count varies for some rooms,
     * then return the count that applies to the majority of the rooms.
     * @deprecated -- Unused as far as I can tell
     *
     */
    public function count_beds_per_room()
    {
        $total = array(); // stores the number of rooms with that many beds

        // Get a list of all the rooms in the hall
        $rdb = new PHPWS_DB('hms_room');

        $rdb->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $rdb->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $rdb->addWhere('hms_residence_hall.id', $this->id);

        $result = $rdb->select();

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        // and for each room get a list of the beds
        foreach ($result as $room) {
            $db = new PHPWS_DB('hms_bed');
            $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
            $db->addWhere('hms_room.id', $room['id']);

            $result = $db->select('count');

            if (PHPWS_Error::logIfError($result)) {
                throw new DatabaseException($result->toString());
            }

            // and increment the count of the number of rooms with that many
            // beds in this hall
            if ($result) {
                $total[$result] = empty($total[$result]) ? 1 : $total[$result] + 1;
            }
        }

        asort($total); // Sort the bed totals by the number of rooms that have each total

        if(!end($total)){  // Jump to the end of the array, return false if array is empty
        	return key($total); // return the last key (the greatest number of beds)
        } else {
        	return null; // There aren't any beds, so we can't find the max
        }
    }

    /*
     * Returns an array of the student objects which are currently assigned to the current hall
    */
    public function get_assignees()
    {
        if (!$this->loadFloors()) {
            return false;
        }

        $assignees = array();

        foreach ($this->_floors as $floor) {
            $floor_assignees = $floor->get_assignees();
            $assignees = array_merge($assignees, $floor_assignees);
        }
        return $assignees;
    }

    /*
     * Returns TRUE if the hall has vacant beds, false otherwise
    */
    public function has_vacancy()
    {
        /*
         if($this->get_number_of_assignees() < $this->get_number_of_beds()) {
        return TRUE;
        }
        */
        $floors = $this->getFloorsWithVacancies();

        if (sizeof($floors) > 0) {
            return true;
        }

        return FALSE;
    }

    /**
     * Returns an array of floor objects in this hall that have vacancies
     */
    public function getFloorsWithVacancies()
    {
        if (!$this->loadFloors()) {
            return false;
        }

        $vacant_floors = array();

        foreach ($this->_floors as $floor) {
            if ($floor->has_vacancy()) {
                $vacant_floors[] = $floor;
            }
        }

        return $vacant_floors;
    }

    public function count_avail_lottery_rooms($gender, $rlcId = null)
    {
        $now = time();

        // Calculate the number of non-full male/female rooms in this hall
        $query = "SELECT COUNT(DISTINCT hms_room.id) FROM hms_room
                    JOIN hms_bed ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
              AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_residence_hall.id = {$this->id}
        			      AND hms_residence_hall.is_online = 1
        			AND hms_floor.is_online = 1
        			AND hms_floor.rlc_id IS NULL
                    AND hms_room.gender_type IN ($gender,3)
                    AND hms_room.reserved = 0
                    AND hms_room.offline = 0
                    AND hms_room.private = 0
                    AND hms_room.overflow = 0
                    AND hms_room.parlor = 0 ";

         if($rlcId != null) {
            $query .= "AND hms_room.reserved_rlc_id = $rlcId ";
         }
         else {
          $query .= "AND hms_room.reserved_rlc_id IS NULL ";
         }

         $query .= "AND hms_bed.international_reserved = 0
                    AND hms_bed.ra = 0
                    AND hms_bed.ra_roommate = 0";



        $avail_rooms = PHPWS_DB::getOne($query);
        if (PHPWS_Error::logIfError($avail_rooms)) {
            throw new DatabaseException($result->toString());
        }

        return $avail_rooms;
    }

    /**
     * Returns an array where each element is an associative sub-array of info for
     * the coordinators of this halll. Returns null if there is no coordinator.
     * NB: There may be multiple people with the coordinator role. This will return
     * the array of all of them.
     */
    public function getCoordinators()
    {
    	return HMS_Permission::getUsersInRoleForInstance('Coordinator', $this);
    }

    /**
     * *******************
     * Getters & Setters *
     */
    public function getBannerBuildingCode()
    {
        return $this->banner_building_code;
    }

    /**
     * Returns the ID of this hall's package desk
     *
     * @return int
     */
    public function getPackageDeskId()
    {
        return $this->package_desk;
    }

    /**
     * Sets the package desk ID for this hall.
     * Id must appear in
     * the 'hms_package_desk' table.
     *
     * @param int $id
     */
    public function setPackageDeskId($id)
    {
        $this->package_desk = $id;
    }

    /**
     * Returns whether a meal plan is required for this residence hall.
     * @return int Integer value 1 if a meal plan is required, 0 if a meal plan is optional
     */
    public function mealPlanRequired(){
        return $this->meal_plan_required;
    }

    /**
     * ****************
     * Static Methods *
     * ***************
     */

    /**
     * Returns an array of hall objects for the given term.
     * If no
     * term is provided, then the current term is used.
     *
     * @deprecated
     *
     * @see ResidenceHallFactory
     */
    public static function get_halls($term)
    {
        $halls = array();

        $db = new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addOrder('hall_name', 'DESC');

        if (isset($term)) {
            $db->addWhere('term', $term);
        }

        $results = $db->select();

        if (PHPWS_Error::logIfError($results)) {
            throw new DatabaseException($result->toString());
        }

        foreach ($results as $result) {
            $halls[] = new HMS_Residence_Hall($result['id']);
        }

        return $halls;
    }

    /**
     * Returns an array with the hall id as the key and the hall name as the value
     *
     * @deprecated
     *
     * @see ResidenceHallFactory
     */
    public static function get_halls_array($term = NULL)
    {
        $hall_array = array();

        $halls = ResidenceHallFactory::getHallsForTerm($term);

        foreach ($halls as $hall) {
            $hall_array[$hall->id] = $hall->hall_name;
        }

        return $hall_array;
    }

    public static function getHallsDropDownValues($term)
    {
        $hall_array = array();

        $halls = ResidenceHallFactory::getHallsForTerm($term);

        $hall_array[0] = 'Select...';

        foreach ($halls as $hall) {
            $hall_array[$hall->id] = $hall->hall_name;
        }

        return $hall_array;
    }

    /**
     * Returns an array of only the halls with vacancies
     */
    public static function getHallsWithVacancies($term)
    {
        $vacant_halls = array();

        $halls = ResidenceHallFactory::getHallsForTerm($term);

        foreach ($halls as $hall) {
            if ($hall->has_vacancy()) {
                $vacant_halls[] = $hall;
            }
        }

        return $vacant_halls;
    }

    /**
     * Returns an array with a key of the hall ID and a value of the hall name
     * for halls which have vacancies
     */
    public static function getHallsWithVacanciesArray($term)
    {
        $hallArray = array();
        $hallArray[0] = 'Select...';

        $halls = HMS_Residence_Hall::getHallsWithVacancies($term);

        foreach ($halls as $hall) {
            $hallArray[$hall->id] = $hall->hall_name;
        }

        return $hallArray;
    }

    /**
     * Returns an associate array (key = hall id, value = hall name) of halls
     * which have an available lottery bed (based on the term, gender, the number
     * of lottery rooms allotted in the hall, the number of used lottery rooms, and
     * any pending lottery bed reservations.
     */
    public static function get_lottery_avail_hall_list($term)
    {
        $halls = HMS_Residence_Hall::get_halls($term);

        $output_list = array();

        foreach ($halls as $hall) {

            // Make sure we have a room of the specified gender available in the hall (or a co-ed room)
            if ($hall->count_avail_lottery_rooms($gender) <= 0 && $hall->count_avail_lottery_rooms(COED) <= 0) {
                continue;
            }

            $output_list[] = $hall;
        }

        return $output_list;
    }
}
