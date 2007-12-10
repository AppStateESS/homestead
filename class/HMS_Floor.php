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
    var $floor_number;
    var $residence_hall_id;
    var $is_online;
    var $gender_type;

    /**
     * List of rooms associated with this floor
     * @vary array
     */
    var $_rooms     = null;

    /**
     * List of suites associated with this floor
     * @var array
     */
    var $_suites    = null;

    /**
     * Holds the parent residence hall object of this floor
     */
    var $_hall      = null;
    
    /**
     * Constructor
     */
    function HMS_Floor($id = 0)
    {
        $this->construct($id, 'hms_floor');
    }

    /********************
     * Instance Methods *
     *******************/

    /*
     * Saves a new or updated floor hall object
     */
    function save()
    {
        $db = new PHPWS_DB('hms_floor');

        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    /*
     * Copies this floor object to a new term, then calls copy on all
     * 'this' floor's rooms/suites
     *
     * Setting $assignments to 'TRUE' causes the copy function to copy
     * the assignments as well as the hall structure.
     *
     * @return bool False if unsuccessful.
     */
    function copy($to_term, $hall_id, $assignments = FALSE)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_floor, copying this floor id: $this->id <br>";

        // Create a clone of the current floor object
        // Set id to 0, set term, and save
        $new_floor = clone($this);
        $new_floor->reset();
        $new_floor->term = $to_term;
        $new_floor->residence_hall_id = $hall_id;

        if(!$new_floor->save()) {
            // There was an error saving the new floor
            echo "error saving a copy of this floor";
            return false;
        }

        // Save successful, create suites

        //echo "loading suites<br>";

        // Load all the suites for this floor
        if(empty($this->_suites)) {
            if($this->loadSuites() === FALSE) {
                // There was an error loading the suites
                echo "error loading suites";
                test($this);
                return false;
            }
        }

        /**
         * Suites exist. Start making copies.
         * Note: No further copying is needed at the suite level!
         */

        if(!empty($this->_suites)) {
            foreach ($this->_suites as $suite) {
                $result = $suite->copy($to_term, $new_floor->id, $assignments);
                // What if bad result?
                test($result);
                test($suite);
                if(!$result){
                    return false;
                    echo "error copying suite";
                }
            }
        }else{
            //echo "No suites to copy<br>";
        }

        // Load all the rooms for this floor which are not in suites
        if(empty($this->_rooms)) {
            $result = $this->loadRooms(0, 0);
            if(!$result) {
                // There was an error loading the rooms
                echo "There was an error loading the rooms";
                test($this);
                return false;
            }else{
                //echo "rooms loaded successfully<br>";
            }
        }

        /**
         * Rooms exist. Start making copies.
         * Further copying is needed at the room level.
         */
        
        if(!empty($this->_rooms)) {
            foreach ($this->_rooms as $room) {
                $result = $room->copy($to_term, $new_floor->id, NULL, $assignments);
                // What if bed result?
                if(!$result){
                    echo "error copying room id: $room->id <br>";
                    test($result);
                    return false;
                }
            }
        }

        return true;
    }
    
    /**
     * Loads the parent hall object of this floor
     */
    function loadHall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $result = new HMS_Residence_Hall($this->residence_hall_id);
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }
        $this->_hall = & $result;
        return true;
    }

    /**
     * Pulls all the suites associated with this floor and stores
     * them in the _suites variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     *
     */
    function loadSuites($deleted=0)
    {
        $db = new PHPWS_DB('hms_suite');
        $db->addWhere('floor_id', $this->id);

        switch ($deleted) {
            case -1:
                $db->addWhere('deleted', 1);
                break;
            case 0:
                $db->addWhere('deleted', 0);
                break;
        }

        $db->loadClass('hms', 'HMS_Suite.php');
        $result = $db->getObjects('HMS_Suite');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_suites = & $result;
            return true;
        }
    }

    /**
     * Pulls all the rooms associated with this floor and stores
     * them in the _room variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     * @param int suites  -1 suites only, 0 no suites only, 1 all rooms
     */
    function loadRooms($deleted = 0, $suites=1)
    {

        $db = new PHPWS_DB('hms_room');
        $db->addWhere('floor_id', $this->id);
        $db->addOrder('room_number', 'ASC');
        switch ($deleted) {
            case -1:
                $db->addWhere('deleted', 1);
                break;
            case 0:
                $db->addWhere('deleted', 0);
                break;
        }

        switch ($suites) {
            case -1:
                $db->addWhere('suite_id', 0, '>');
                break;
            case 0:
                $db->addWhere('suite_id', NULL, 'IS NULL');
                break;
        }

        $db->loadClass('hms', 'HMS_Room.php');
        $result = $db->getObjects('HMS_Room');
        //test($result);
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_rooms = & $result;
            return true;
        }
    }

    /*
     * Creates the rooms, bedrooms, and beds for a new floor
     */
    function create_child_objects($rooms_per_floor, $bedrooms_per_room, $beds_per_bedroom)
    {
        for ($i = 0; $i < $rooms_per_floor; $i++) {
            $room = new HMS_Room;

            $room->floor_id     = $this->id;
            $room->term         = $this->term;
            $room->gender_type  = $this->gender_type;

            if($room->save()) {
                $room->create_child_objects($bedrooms_per_room, $beds_per_bedroom);
            } else {
                // Decide on bad Result.
            }
        }
    }

    /*
     * Returns TRUE or FALSE. The gender of a floor can only be changed to the
     * target gender if all rooms can be changed to the target gender.
     *
     * Additionally, the floor's gender can only be changed if the target
     * gender will be consistent with the gender of the hall of which
     * this floor is a part.
     *
     * This function checks to make sure all rooms can be changed,
     * those rooms in tern check all thier bedrooms, and so on.
     *
     * In the case that we're attempting to change the gender of just
     * 'this' floor, set $ignore_upper to TRUE to avoid checking the
     * parent hall's gender.
     */
    #TODO: Implement the $ignore_upper flag.
    function can_change_gender($target_gender, $ignore_upper = FALSE)
    {
        if ($target_gender != COED) {
            $this->loadRooms();
            if ($this->_rooms) {
                foreach ($this->_rooms as $room) {
                    // If the bedroom gender type is not coed and the bedroom gt
                    // does not equal the target gender, we return false
                    if ($room->gender_type != COED && $room->gender_type != $target_gender) {
                        return false;
                    }
                }
            }
        }

        if (!$ignore_upper) {
            if (!$this->loadHall()) {
                // an error occurred loading the hall, check logs
                return false;
            }
            // If the floor is not coed and the gt is not the target, return false
            if ($this->_hall->gender_type != COED && $this->_hall->gender_type != $target_gender) {
                return false;
            }
        }
    }

    /*
     * Returns the number of rooms on the current floor
     */
    function get_number_of_rooms()
    {
        $db = &new PHPWS_DB('hms_room');

        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');

        $db->addWhere('hms_room.deleted', 0);
        $db->addWhere('hms_floor.deleted', 0);

        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of suites on the current floor
     */
    function get_number_of_suites()
    {
        $db = &new PHPWS_DB('hms_suite');

        $db->addJoin('LEFT OUTER', 'hms_suite', 'hms_floor', 'floor_id', 'id');

        $db->addWhere('hms_suite.deleted', 0);
        $db->addWhere('hms_floor.deleted', 0);

        $db->addWhere('hms_floor.id', $this->id);
        
        $result = $db->select('count');

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of bedrooms on the current floor
     */
    function get_number_of_bedrooms()
    {
        $db = &new PHPWS_DB('hms_bedroom');
        
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        
        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;

    }

    /*
     * Returns the number of beds on the current floor
     */
    function get_number_of_beds()
    {
        $db = &new PHPWS_DB('hms_bed');
        
        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_bedroom',        'bedroom_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        
        $db->addWhere('hms_bed.deleted',            0);
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        
        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of assignees on the current floor
     */
    function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');
        
        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed',            'bed_id',            'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_bedroom',        'bedroom_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        
        $db->addWhere('hms_assignment.deleted',     0);
        $db->addWhere('hms_bed.deleted',            0);
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        
        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the parent hall object of this floor
     */
    function get_parent()
    {
        $this->loadHall();
        return $this->_hall;
    }

    /*
     * Returns an array of the rooms on the current floor
     */
    function get_rooms()
    {
        if (!$this->loadRooms()) {
            return false;
        }

        return $this->_rooms;
    }

    /**
     * Returns an array of the suites on the current floor
     */
    function get_suites()
    {
        if(!$this->loadSuites()) {
            return false;
        }

        return $this->_suites;
    }

    /**
     * Returns an array of the bedrooms on the current floor
     */
    function get_bedrooms()
    {
        $bedrooms = array();
        
        if (!$this->loadRooms()){
            return false;
        }

        foreach($this->_rooms as $room){
            $room_bedrooms = $room->get_bedrooms();
            $bedrooms = array_merge($bedrooms, $room_bedrooms);
        }
        return $bedrooms;
    }

    /**
     * Returns an array of the beds on the current floor
     */
    function get_beds()
    {
        $beds = array();

        if (!$this->loadRooms()){
            return false;
        }

        foreach($this->_rooms as $room){
            $room_beds = $room->get_beds();
            $beds = array_merge($beds, $room_beds);
        }
        return $beds;
    }

    /**
     * Returns an array of student objects which are currently assigned to this floor
     */
    function get_assignees()
    {
        if (!$this->loadRooms()) {
            return false;
        }

        $assignees = array();

        foreach($this->_rooms as $room){
            $room_assignees = $room->get_assignees();
            $assignees = array_merge($assignees, $room_assignees);
        }

        return $assignees;
    }

    /**
     * Returns TRUE if this floor has vancancies, FALSE otherwise
     */
    function has_vacancy()
    {
        if($this->get_number_of_assignees() < $this->get_number_of_beds()){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Returns an array of room objects on this floor that have vacancies
     */
    function get_rooms_with_vacancies()
    {
        if(!$this->loadRooms()) {
            return FALSE;
        }

        $vacant_room = array();

        foreach($this->_rooms as $room){
            if($room->has_vacancy()){
                $vacant_rooms[] = $room;
            }
        }

        return $vacant_rooms;
    }

    /**
     * Main Method
     */
    function main()
    {

    }

    /******************
     * Static Methods *
     *****************/

    function floor_pager()
    {

    }

    function get_row_tags()
    {

    }

    /*******************
     * Mutator Methods *
     ******************/

    function set_id($id){
        $this->id = $id;
    }

    function get_id(){
        return $this->id;
    }

    function set_term($term){
        $this->term = $term;
    }

    function get_term(){
        return $this->term;
    }

    function set_floor_number($num){
        $this->floor_number = $num;
    }

    function get_floor_number(){
        return $this->floor_number;
    }

    function set_residence_hall_id($id){
        $this->residence_hall_id = $id;
    }

    function get_residence_hall_id(){
        return $this->residence_hall_id;
    }

    function set_is_online($status){
        $this->is_online = $status;
    }

    function get_is_online(){
        return $this->is_online;
    }

    function set_gender_type($gender){
        $this->gender_type = $gender;
    }

    function get_gender_type(){
        return $this->gender_type;
    }
    
    function set_added_by($user_id = NULL){
        if(isset($user_id)){
            $this->added_by = $user_id;
        }else{
            $this->added_by = Current_User::getId();
        }
    }

    function get_added_by(){
        return $this->added_by;
    }

    function set_added_on($timestamp = NULL){
        if(isset($timestamp)){
            $this->added_on = $timestamp;
        }else{
            $this->added_on = mktime();
        }
    }

    function get_added_on(){
        return $this->added_on;
    }

    function set_updated_by($user_id = NULL){
        if(isset($user_id)){
            $this->updated_by = $user_id;
        }else{
            $this->updated_by = Current_User::getId();
        }
    }

    function get_updated_by(){
        return $this->updated_by;
    }

    function set_updated_on($timestamp = NULL){
        if(isset($timestamp)){
            $this->updated_on = $timestamp;
        }else{
            $this->updated_on = mktime();
        }
    }

    function get_updated_on(){
        return $this->updated_on;
    }

    function set_deleted_by($user_id = NULL){
        if(isset($user_id)){
            $this->deleted_by = $user_id;
        }else{
            $this->deleted_by = Current_User::getId();
        }
    }

    function get_deleted_by(){
        return $this->deleted_by;
    }

    function set_deleted_on($timestamp = NULL){
        if(isset($timestamp)){
            $this->deleted_on = $timestamp;
        }else{
            $this->deleted_on = mktime();
        }
    }

    function get_deleted_on(){
        return $this->deleted_on;
    }

    function set_deleted($del = 1){
        $this->deleted = $del;
    }

    function get_deleted(){
        return $this->deleted;
    }
}
?>
