<?php

/**
 * HMS Residence Hall class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Residence_Hall extends HMS_Item
{
    public $hall_name                  = NULL;
    public $term;
    public $banner_building_code       = NULL;


    public $gender_type                = 2;
    public $air_conditioned            = 0;
    public $is_online                  = 0;

    public $meal_plan_required         = 0;
    public $assignment_notifications   = 1;

    // Photo IDs
    public $exterior_image_id;
    public $other_image_id;
    public $map_image_id;
    public $room_plan_image_id;

    /**
     * Listing of floors associated with this room
     * @var array
     */
    public $_floors                = null;

    /**
     * Temporary values for rh creation
     */
    public $_number_of_floors      = 0;
    public $_rooms_per_floor       = 0;
    public $_beds_per_room         = 0;
    public $_numbering_scheme      = 0;

    /**
     * Constructor
     */
    public function HMS_Residence_Hall($id = 0)
    {
        $this->construct($id, 'hms_residence_hall');
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_residence_hall');
    }

    /********************
     * Instance Methods *
     *******************/

    /*
     * Saves a new or updated residence hall object
     */
    public function save()
    {
        $this->stamp();
        $db = new PHPWS_DB('hms_residence_hall');
        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
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
        if(!$this->id) {
            return false;
        }

        //echo "In hms_residence_hall, copying this hall: $this->id <br>";

        // Create clone of current room object
        // Set id to 0, set term, and save
        $new_hall = clone($this);
        $new_hall->reset();
        $new_hall->id   = 0;
        $new_hall->term = $to_term;

        try{
            $new_hall->save();
        }catch(Exception $e){
            // rethrow it to the top level
            throw $e;
        }

        // Copy any roles related to this residence hall.
        if($roles){
            PHPWS_Core::initModClass("hms", "HMS_Permission.php");
            PHPWS_Core::initModClass("hms", "HMS_Role.php");
            // Get memberships by object instance.
            $membs = HMS_Permission::getUserRolesForInstance($this);
            //test($membs,1);
            // Add each user to new hall
            foreach($membs as $m){
                // Lookup the username
                $user = new PHPWS_User($m['user_id']);

                // Load role and add user to new instance
                $role = new HMS_Role();
                $role->id = $m['role'];
                $role->load();
                $role->addUser($user->getUsername(), get_class($new_hall), $new_hall->id);
            }
        }

        // Save successful, create new floors

        // Load all floors for this hall
        if(empty($this->_floors)) {
            try{
                $this->loadFloors();
            }catch(Exception $e){
                throw $e;
            }
        }

        // Floors exist, start making copies
        if(!empty($this->_floors)) {
            foreach ($this->_floors as $floor) {
                try{
                    $floor->copy($to_term, $new_hall->id, $assignments, $roles);
                }catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    /**
     * Pulls all the floors associated with this hall and stores them in
     * the _floors variable.
     *
     */
    public function loadFloors()
    {
        if(!$this->id) {
            $this->_floor = null;
            return null;
        }

        $db = new PHPWS_DB('hms_floor');
        $db->addWhere('residence_hall_id', $this->id);
        $db->addOrder('floor_number', 'ASC');

        $db->loadClass('hms', 'HMS_Floor.php');
        $result = $db->getObjects('HMS_Floor');
        if(PHPWS_Error::logIfError($result)) {
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
        if(!$this->id) {
            return false;
        }

        for ($i = 0; $i < $num_floors; $i++) {
            $floor = new HMS_Floor;

            $floor->residence_hall_id   = $this->id;
            $floor->term                = $this->term;
            $floor->gender_type         = $this->gender_type;

            if($floor->save()){
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
    #TODO: rewrite this becase the behavior changed
    public function can_change_gender($target_gender)
    {
        # You can always change to a COED gender.
        if($target_gender == COED){
            return true;

        }

        # We must be changing to either male or female if we make it here

        # If there are any COED floors, then return false
        if($this->check_for_floors_of_gender(COED)){
            return false;
        }

        # Can only change gender if there are no floors of the opposite sex
        if($target_gender == MALE){
            $check_for_gender = FEMALE;
        }else{
            $check_for_gender = MALE;
        }

        # If a check for rooms of the opposite gender returns true, then return false
        if($this->check_for_floors_of_gender($check_for_gender)){
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

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if($result == 0){
            return false;
        }else{
            return true;
        }
    }

    public function getId(){
        return $this->id;
    }

    public function getHallName(){
        return $this->hall_name;
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
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)){
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

        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if($result == 0){
            return 0;
        }
        
        if(!$result || PHPWS_Error::logIfError($result)){
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

        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_room',           'room_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if($result == 0){
            return 0;
        }
        
        if(!$result || PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result);
        }

        return $result;

    }

    public function get_number_of_online_nonoverflow_beds()
    {
        $db = new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_room',           'room_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);
        $db->addWhere('hms_room.offline', 0);
        $db->addWhere('hms_room.overflow', 0);

        $result = $db->select('count');

        if($result == 0){
            return 0;
        }
        
        if(PHPWS_Error::logIfError($result)){
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

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed',             'bed_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',        'hms_room',            'room_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_room',       'hms_floor',           'floor_id',         'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',      'hms_residence_hall',  'residence_hall_id','id');

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if($result == 0){
            return 0;
        }
        
        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if($result == 0){
            return $result;
        }

        return $result;
    }

    /*
     * Returns an array of floor objects which are within the current hall.
     */
    public function &get_floors()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        return $this->_floors;
    }


    /*
     * Returns an array with the keys being floor ID's and the value being the floor number
     */
    public function get_floors_array()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $floors = array();

        foreach($this->_floors as $floor){
            $floors[$floor->id] = $floor->floor_number;
        }

        return $floors;
    }

    /*
     * Returns an array of room objects which are in the current hall
     */
    public function &get_rooms()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $rooms = array();

        foreach($this->_floors as $floor){
            $floor_rooms = $floor->get_rooms();
            $rooms = array_merge($rooms, $floor_rooms);
        }
        return $rooms;
    }

    /*
     * Returns an array of the bed objects which are in the current hall
     */
    public function &get_beds()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $beds = array();

        foreach($this->_floors as $floor){
            $floor_beds = $floor->get_beds();
            $beds = array_merge($rooms, $floor_beds);
        }
        return $beds;
    }

    /*
     * Determines the number of beds per room in a hall.  Should the count vary
     * it returns the count that applies to the majority of the rooms.
     */
    public function count_beds_per_room()
    {
        $total = array(); //stores the number of rooms with that many beds

        //Get a list of all the rooms in the hall
        $rdb = new PHPWS_DB('hms_room');

        $rdb->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $rdb->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $rdb->addWhere('hms_residence_hall.id', $this->id);

        $result = $rdb->select();

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        //and for each room get a list of the beds
        foreach($result as $room){
            $db = new PHPWS_DB('hms_bed');
            $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
            $db->addWhere('hms_room.id',           $room['id']);

            $result = $db->select('count');

            if(PHPWS_Error::logIfError($result)){
                throw new DatabaseException($result->toString());
            }

            //and increment the count of the number of rooms with that many
            //beds in this hall
            if($result){
                $total[$result] = empty($total[$result]) ? 1 : $total[$result]+1;
            }
        }

        $top   = 0;
        foreach($total as $key => $value){
            if(@$total[$key] > @$total[$top]){ // supress notices here, since usually there's an undefined index
                $top = $key;
            }
        }

        return $top;
    }

    /*
     * Returns an array of the student objects which are currently assigned to the current hall
     */
    public function get_assignees()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $assignees = array();

        foreach($this->_floors as $floor){
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
        if($this->get_number_of_assignees() < $this->get_number_of_beds()){
            return TRUE;
        }
        */

        $floors = $this->getFloorsWithVacancies();

        if(sizeof($floors) > 0)
        {
            return true;
        }

        return FALSE;
    }

    /**
     * Returns an array of floor objects in this hall that have vacancies
     */
    public function getFloorsWithVacancies()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $vacant_floors = array();

        foreach($this->_floors as $floor){
            if($floor->has_vacancy()){
                $vacant_floors[] = $floor;
            }
        }

        return $vacant_floors;
    }

    public function count_avail_lottery_rooms($gender)
    {
        $now = mktime();

        # Calculate the number of non-full male/female rooms in this hall
        $query =   "SELECT COUNT(DISTINCT hms_room.id) FROM hms_room
                    JOIN hms_bed ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                    AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_residence_hall.id = {$this->id}
                    AND hms_room.gender_type = $gender
                    AND hms_room.reserved = 0
                    AND hms_room.offline = 0
                    AND hms_room.private = 0
                    AND hms_room.ra = 0
                    AND hms_room.overflow = 0
                    AND hms_room.parlor = 0
                    AND hms_bed.international_reserved = 0
                    AND hms_floor.rlc_id IS NULL";

        $avail_rooms = PHPWS_DB::getOne($query);
        if(PHPWS_Error::logIfError($avail_rooms)){
            throw new DatabaseException($result->toString());
        }

        return $avail_rooms;
    }

/*
    public function count_lottery_used_rooms()
    {
        $now = mktime();

        $query = "SELECT count(hms_room.*) FROM hms_room
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                       AND hms_residence_hall.id = {$this->id} AND
                       hms_room.id IN (SELECT DISTINCT hms_room.id FROM hms_room
                       JOIN hms_bed ON hms_bed.room_id = hms_room.id
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                       WHERE (hms_bed.id IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                       OR hms_bed.id IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term} and lottery = 1))
                       AND hms_residence_hall.id = {$this->id})";

        //test(preg_replace("/\s+/", ' ',$query),1);

        $used_rooms = PHPWS_DB::getOne($query);
        if(PHPWS_Error::logIfError($used_rooms)){
            throw new DatabaseException($used_rooms->toString());
        }

        return $used_rooms;
    } */

/*
    public function count_lottery_full_rooms()
    {
        $now = mktime();

        # Get the number of rooms in this hall which have every bed either assigned or reserved through the lottery.
        $query      = "SELECT count(hms_room.*) FROM hms_room
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                       WHERE
                       hms_residence_hall.id = {$this->id} AND
                       hms_room.id NOT IN (SELECT DISTINCT hms_room.id FROM hms_room
                        JOIN hms_bed ON hms_bed.room_id = hms_room.id
                        JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                        JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                        WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                        AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term} and lottery = 1))
                        AND hms_residence_hall.id = {$this->id})";

        $used_rooms = PHPWS_DB::getOne($query);
        if(PHPWS_Error::logIfError($used_rooms)){
            throw new DatabaseException($result->toString());
        }

        return $used_rooms;
    }*/

    /**
     * Returns the pager tags for the db pager
     *
     * Is this even used anywhere??
     */
    public function get_row_tags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tags = $this->item_tags();
        $tags['HALL_NAME'] = $this->hall_name;
        $tags['BANNER_BUILDING_CODE'] = $this->banner_building_code;

        $is_online = $this->get_is_online();
        if($is_online == ONLINE) {
            $tags['IS_ONLINE'] = ONLINE_DESC;
        }else if($is_online == OFFLINE){
            $tags['IS_ONLINE'] = OFFLINE_DESC;
        }else{
            $tags['IS_ONLINE'] = 'Error: Unknown status';
        }

        $gender_type = $this->get_gender_type();
        $tags['GENDER_TYPE'] = HMS_Util::formatGender($this->gender_type);

        #$num_beds = $this->get_number_of_beds();
        #$num_assignees = $this->get_number_of_assignees();
        #$num_beds_free = $num_beds - $num_beds_free;

        #$tags['NUM_FLOORS']     = $this->get_number_of_floors();
        #$tags['NUM_ROOMS']      = $this->get_number_of_rooms();
        #$tags['NUM_BEDS']       = $num_beds;
        #$tags['NUM_ASSIGNEES']  = $num_assignees();
        #$tags['NUM_BEDS_FREE']  = $num_beds_free();
        $tags['ACTIONS'] = 'View Delete'; #TODO
        return $tags;
    }

    /*********************
     * Getters & Setters *
     */
    public function getBannerBuildingCode()
    {
        return $this->banner_building_code;
    }

    /******************
     * Static Methods *
     *****************/

    /**
     * Returns an array of hall objects for the given term. If no
     * term is provided, then the current term is used.
     */
    public static function get_halls($term)
    {
        $halls = array();

        $db = new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addOrder('hall_name', 'DESC');

        if(isset($term)){
            $db->addWhere('term', $term);
        }

        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException($result->toString());
        }

        foreach($results as $result){
            $halls[] = new HMS_Residence_Hall($result['id']);
        }

        return $halls;
    }

    /**
     * Returns an array with the hall id as the key and the hall name as the value
     */
    public static function get_halls_array($term = NULL)
    {
        $hall_array = array();

        $halls = HMS_Residence_Hall::get_halls($term);

        foreach ($halls as $hall){
            $hall_array[$hall->id] = $hall->hall_name;
        }

        return $hall_array;
    }

    public static function getHallsDropDownValues($term)
    {
        $hall_array = array();

        $halls = HMS_Residence_Hall::get_halls($term);

        $hall_array[0] = 'Select...';

        foreach ($halls as $hall){
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

        $halls = HMS_Residence_Hall::get_halls($term);

        foreach($halls as $hall){
            if($hall->has_vacancy()){
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

        foreach ($halls as $hall){
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

        foreach($halls as $hall){
            $rooms_used = $hall->count_lottery_used_rooms();

            # Make sure we have a room of the specified gender available in the hall (or a co-ed room)
            if($hall->count_avail_lottery_rooms($gender) <= 0 &&
            $hall->count_avail_lottery_rooms(COED) <= 0){
                continue;
            }

            $output_list[] = $hall;
        }

        return $output_list;
    }

    /**
     * Returns the HTML for a DB pager of the current set of
     * residence halls available.
     * Is this even used anywehre??
     */
    public static function residence_hall_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('hms_residence_hall','HMS_Residence_Hall');
        $pager->db->addOrder('hall_name','DESC');
        #TODO: $pager->db->addWhere('term', SOMETHING);

        $pager->setModule('hms');
        $pager->setTemplate('admin/residence_hall_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No residence halls found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('get_row_tags');

        return $pager->get();
    }
}
?>
